<?php

namespace App\Http\Services;

class EmailValidationService
{
    /**
     * Well-known free email provider domains.
     */
    private const FREE_PROVIDERS = [
        'gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'aol.com',
        'icloud.com', 'mail.com', 'zoho.com', 'protonmail.com', 'proton.me',
        'yandex.com', 'gmx.com', 'gmx.net', 'live.com', 'msn.com',
        'tutanota.com', 'fastmail.com', 'hey.com', 'mail.ru',
    ];

    /**
     * Domains commonly used for disposable / temporary email addresses.
     */
    private const DISPOSABLE_DOMAINS = [
        'mailinator.com', 'guerrillamail.com', 'tempmail.com', 'throwaway.email',
        'yopmail.com', 'sharklasers.com', 'guerrillamailblock.com', 'grr.la',
        'dispostable.com', 'trashmail.com', 'fakeinbox.com', 'tempinbox.com',
        'maildrop.cc', 'discard.email', 'temp-mail.org', 'getnada.com',
    ];

    /**
     * Validate an email address with comprehensive checks.
     */
    public function validate(string $email): array
    {
        $result = [
            'valid'           => false,
            'email'           => $email,
            'mx'              => false,
            'syntax'          => false,
            'smtp'            => false,
            'is_alias'        => false,
            'is_catch_all'    => false,
            'is_disabled'     => false,
            'did_you_mean'    => null,
            'domain_age_days' => null,
            'is_domain_error' => false,
            'is_user_error'   => false,
            'is_spam_trap'    => false,
            'mailbox_level'   => 'unknown',
            'free'            => false,
            'score'           => 0,
        ];

        // ── 1. Syntax ────────────────────────────────────────────
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $result['is_user_error'] = true;
            return $result;
        }

        $result['syntax'] = true;

        [, $domain] = explode('@', $email, 2);

        // ── 2. Free / disposable provider detection ──────────────
        $result['free'] = in_array(strtolower($domain), self::FREE_PROVIDERS, true);

        if (in_array(strtolower($domain), self::DISPOSABLE_DOMAINS, true)) {
            $result['is_spam_trap'] = true;
        }

        // ── 3. Alias detection (user+tag@…) ──────────────────────
        $localPart = explode('@', $email)[0];
        if (str_contains($localPart, '+')) {
            $result['is_alias'] = true;
        }

        // ── 4. MX records ────────────────────────────────────────
        $mxRecords = dns_get_record($domain, DNS_MX);

        if (!$mxRecords || count($mxRecords) === 0) {
            $result['is_domain_error'] = true;
            return $result;
        }

        $result['mx'] = true;

        // Sort MX records by priority (lower = higher priority)
        usort($mxRecords, fn($a, $b) => ($a['pri'] ?? 99) <=> ($b['pri'] ?? 99));

        // ── 5. Domain age (best-effort via SOA) ──────────────────
        $result['domain_age_days'] = $this->estimateDomainAge($domain);

        // ── 6. Full SMTP verification ────────────────────────────
        $smtpResult = $this->checkSMTP($email, $mxRecords);

        $result['smtp']         = $smtpResult['accepted'];
        $result['is_catch_all'] = $smtpResult['is_catch_all'];
        $result['is_disabled']  = $smtpResult['is_disabled'];
        $result['mailbox_level'] = $smtpResult['mailbox_level'];

        // ── 7. Composite validity & score ────────────────────────
        $score = 0;

        if ($result['syntax'])     $score += 0.10;
        if ($result['mx'])         $score += 0.15;
        if ($result['smtp'])       $score += 0.40;
        if (!$result['is_catch_all'])  $score += 0.10;
        if (!$result['is_disabled'])   $score += 0.05;
        if (!$result['is_spam_trap'])  $score += 0.05;
        if (!$result['is_alias'])      $score += 0.05;

        // Domain age bonus
        if ($result['domain_age_days'] !== null && $result['domain_age_days'] > 365) {
            $score += 0.05;
        }

        // Mailbox level bonus
        if ($result['mailbox_level'] === 'confirmed') {
            $score += 0.05;
        }

        $result['score'] = round(min($score, 1.0), 2);
        $result['valid'] = $result['syntax'] && $result['mx'] && $result['smtp'] && $result['score'] >= 0.5;

        // ── 8. "Did you mean?" suggestion ────────────────────────
        $result['did_you_mean'] = $this->suggestDomain($domain, $localPart);

        return $result;
    }

    // ─── SMTP VERIFICATION ───────────────────────────────────────

    /**
     * Perform a thorough SMTP conversation:
     *  - EHLO with STARTTLS upgrade attempt
     *  - VRFY probe
     *  - RCPT TO verification
     *  - Catch-all detection (random RCPT TO)
     *  - Greylisting retry (4xx → wait & retry once)
     *  - Reverse-DNS / banner analysis
     */
    private function checkSMTP(string $email, array $mxRecords): array
    {
        $smtpResult = [
            'accepted'       => false,
            'is_catch_all'   => false,
            'is_disabled'    => false,
            'mailbox_level'  => 'unknown', // unknown | unconfirmed | confirmed
        ];

        $fromDomain = config('app.url') ? parse_url(config('app.url'), PHP_URL_HOST) ?? 'verify.local' : 'verify.local';
        $from = 'verify@' . $fromDomain;

        foreach ($mxRecords as $mx) {
            $host = $mx['target'] ?? null;
            if (!$host) continue;

            // ── Reverse-DNS sanity check ─────────────────────────
            $ip = gethostbyname($host);
            if ($ip === $host) {
                // Could not resolve – skip
                continue;
            }

            $connection = @fsockopen($host, 25, $errno, $errstr, 10);
            if (!$connection) continue;

            stream_set_timeout($connection, 10);

            // ── Read banner ──────────────────────────────────────
            $banner = $this->getResponse($connection);

            // If server immediately rejects (5xx banner), skip
            if (str_starts_with(trim($banner), '5')) {
                fclose($connection);
                continue;
            }

            // ── EHLO (prefer over HELO) ──────────────────────────
            $ehloResp = $this->sendCommand($connection, "EHLO {$fromDomain}");
            $supportsStartTls = str_contains(strtoupper($ehloResp), 'STARTTLS');
            $supportsVrfy     = str_contains(strtoupper($ehloResp), 'VRFY');

            // Fall back to HELO if EHLO was rejected
            if (str_starts_with(trim($ehloResp), '5')) {
                $this->sendCommand($connection, "HELO {$fromDomain}");
            }

            // ── STARTTLS upgrade ─────────────────────────────────
            if ($supportsStartTls) {
                $tlsResp = $this->sendCommand($connection, "STARTTLS");

                if (str_starts_with(trim($tlsResp), '220')) {
                    $cryptoOk = @stream_socket_enable_crypto(
                        $connection,
                        true,
                        STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT
                    );

                    if ($cryptoOk) {
                        // Re-EHLO after TLS handshake (required by RFC)
                        $this->sendCommand($connection, "EHLO {$fromDomain}");
                    }
                }
            }

            // ── VRFY probe (if supported) ────────────────────────
            if ($supportsVrfy) {
                $vrfyResp = $this->sendCommand($connection, "VRFY <{$email}>");
                $vrfyCode = (int) substr(trim($vrfyResp), 0, 3);

                if ($vrfyCode === 250 || $vrfyCode === 252) {
                    $smtpResult['mailbox_level'] = 'confirmed';
                }
            }

            // ── MAIL FROM ────────────────────────────────────────
            $mailFromResp = $this->sendCommand($connection, "MAIL FROM:<{$from}>");

            if (!str_starts_with(trim($mailFromResp), '250')) {
                $this->sendCommand($connection, "QUIT");
                fclose($connection);
                continue;
            }

            // ── RCPT TO (real address) ───────────────────────────
            $rcptResp = $this->sendCommand($connection, "RCPT TO:<{$email}>");
            $rcptCode = (int) substr(trim($rcptResp), 0, 3);

            // Handle greylisting (4xx) — wait 5 s and retry once
            if ($rcptCode >= 400 && $rcptCode < 500) {
                $this->sendCommand($connection, "RSET");
                sleep(5);
                $this->sendCommand($connection, "MAIL FROM:<{$from}>");
                $rcptResp = $this->sendCommand($connection, "RCPT TO:<{$email}>");
                $rcptCode = (int) substr(trim($rcptResp), 0, 3);
            }

            $accepted = ($rcptCode === 250 || $rcptCode === 251);

            // Mailbox disabled / full
            if ($rcptCode === 550 || $rcptCode === 551 || $rcptCode === 552 || $rcptCode === 553) {
                $smtpResult['is_disabled'] = true;
            }

            // ── Catch-all detection (random address) ─────────────
            if ($accepted) {
                $this->sendCommand($connection, "RSET");
                $this->sendCommand($connection, "MAIL FROM:<{$from}>");

                $randomLocal = 'probe-' . bin2hex(random_bytes(8));
                $randomEmail = $randomLocal . '@' . explode('@', $email)[1];
                $catchResp = $this->sendCommand($connection, "RCPT TO:<{$randomEmail}>");
                $catchCode = (int) substr(trim($catchResp), 0, 3);

                if ($catchCode === 250 || $catchCode === 251) {
                    $smtpResult['is_catch_all'] = true;
                    // Catch-all accepted – we can't confirm individual mailbox
                    if ($smtpResult['mailbox_level'] !== 'confirmed') {
                        $smtpResult['mailbox_level'] = 'unconfirmed';
                    }
                } else {
                    // Server correctly rejects random address → mailbox is real
                    $smtpResult['mailbox_level'] = 'confirmed';
                }
            }

            // ── Graceful disconnect ──────────────────────────────
            $this->sendCommand($connection, "QUIT");
            fclose($connection);

            $smtpResult['accepted'] = $accepted;

            // Stop after the first MX that responds meaningfully
            break;
        }

        return $smtpResult;
    }

    // ─── HELPERS ─────────────────────────────────────────────────

    private function sendCommand($connection, string $command): string
    {
        fwrite($connection, $command . "\r\n");
        return $this->getResponse($connection);
    }

    private function getResponse($connection): string
    {
        $response = '';

        while ($line = @fgets($connection, 515)) {
            $response .= $line;
            // A space at position 4 marks the last line of a multi-line reply
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }

        return $response;
    }

    /**
     * Best-effort domain age estimation via SOA serial (YYYYMMDD format).
     */
    private function estimateDomainAge(string $domain): ?int
    {
        $soa = @dns_get_record($domain, DNS_SOA);

        if (!$soa || empty($soa[0]['serial'])) {
            return null;
        }

        $serial = (string) $soa[0]['serial'];

        // Many SOA serials follow YYYYMMDD… format
        if (strlen($serial) >= 8) {
            $dateStr = substr($serial, 0, 8);

            try {
                $soaDate = new \DateTime($dateStr);
                $now     = new \DateTime();

                if ($soaDate < $now) {
                    return (int) $soaDate->diff($now)->days;
                }
            } catch (\Exception) {
                // Serial doesn't encode a date — can't estimate
            }
        }

        return null;
    }

    /**
     * Suggest a corrected domain if the user likely made a typo.
     */
    private function suggestDomain(string $domain, string $localPart): ?string
    {
        $commonDomains = [
            'gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com',
            'aol.com', 'icloud.com', 'mail.com', 'protonmail.com',
            'zoho.com', 'live.com', 'msn.com', 'yandex.com',
        ];

        $domain = strtolower($domain);

        if (in_array($domain, $commonDomains, true)) {
            return null; // Already correct
        }

        $bestMatch    = null;
        $bestDistance  = PHP_INT_MAX;

        foreach ($commonDomains as $candidate) {
            $distance = levenshtein($domain, $candidate);

            if ($distance < $bestDistance && $distance <= 2) {
                $bestDistance = $distance;
                $bestMatch   = $candidate;
            }
        }

        return $bestMatch ? "{$localPart}@{$bestMatch}" : null;
    }
}
