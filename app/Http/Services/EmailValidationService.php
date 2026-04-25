<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmailValidationService
{
    private const FREE_PROVIDERS = [
        'gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'aol.com',
        'icloud.com', 'mail.com', 'zoho.com', 'protonmail.com', 'proton.me',
        'yandex.com', 'gmx.com', 'gmx.net', 'live.com', 'msn.com',
        'tutanota.com', 'fastmail.com', 'hey.com', 'mail.ru',
    ];

    private const DISPOSABLE_DOMAINS = [
        'mailinator.com', 'guerrillamail.com', 'tempmail.com', 'throwaway.email',
        'yopmail.com', 'sharklasers.com', 'guerrillamailblock.com', 'grr.la',
        'dispostable.com', 'trashmail.com', 'fakeinbox.com', 'tempinbox.com',
        'maildrop.cc', 'discard.email', 'temp-mail.org', 'getnada.com',
    ];

    private bool $smtpAvailable;

    public function __construct()
    {
        $this->smtpAvailable = $this->checkSmtpAvailability();
    }

    private function checkSmtpAvailability(): bool
    {
        $test = @fsockopen('gmail-smtp-in.l.google.com', 25, $errno, $errstr, 3);
        if ($test) {
            fclose($test);
            return true;
        }
        return false;
    }

    public function isSmtpAvailable(): bool
    {
        return $this->smtpAvailable;
    }

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
            'free'           => false,
            'score'          => 0,
            'source'         => 'internal',
        ];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $result['is_user_error'] = true;
            return $result;
        }

        $result['syntax'] = true;

        [, $domain] = explode('@', $email, 2);
        $domain = strtolower($domain);

        $result['free'] = in_array($domain, self::FREE_PROVIDERS, true);

        if (in_array($domain, self::DISPOSABLE_DOMAINS, true)) {
            $result['is_spam_trap'] = true;
        }

        $localPart = explode('@', $email)[0];
        if (str_contains($localPart, '+')) {
            $result['is_alias'] = true;
        }

        $mxRecords = @dns_get_record($domain, DNS_MX);

        if (!$mxRecords || count($mxRecords) === 0) {
            $result['is_domain_error'] = true;
            
            // Fallback: try external API when MX fails or is unavailable
            if ($result['free']) {
                $result['mx'] = true;
                $fallback = $this->validateWithExternalApi($email);
                $result = array_merge($result, $fallback);
                $result = $this->calculateScore($result);
                $result['did_you_mean'] = $this->suggestDomain($domain, $localPart);
                return $result;
            }
            
            return $result;
        }

        $result['mx'] = true;
        usort($mxRecords, fn($a, $b) => ($a['pri'] ?? 99) <=> ($b['pri'] ?? 99));

        $result['domain_age_days'] = $this->estimateDomainAge($domain);

        if ($this->smtpAvailable) {
            $smtpResult = $this->checkSMTP($email, $mxRecords);
            $result['smtp']         = $smtpResult['accepted'];
            $result['is_catch_all'] = $smtpResult['is_catch_all'];
            $result['is_disabled']  = $smtpResult['is_disabled'];
            $result['mailbox_level'] = $smtpResult['mailbox_level'];
        } else {
            $fallback = $this->validateWithExternalApi($email);
            $result = array_merge($result, $fallback);
        }

        $result = $this->calculateScore($result);
        $result['did_you_mean'] = $this->suggestDomain($domain, $localPart);

        return $result;
    }

    private function calculateScore(array $result): array
    {
        $score = 0;

        if ($result['syntax'])     $score += 0.15;
        if ($result['mx'])         $score += 0.20;
        if ($result['smtp'])         $score += 0.35;
        if ($result['free'])        $score += 0.10;
        if (!$result['is_catch_all'])  $score += 0.05;
        if (!$result['is_disabled'])   $score += 0.05;
        if (!$result['is_spam_trap'])  $score += 0.05;

        if ($result['domain_age_days'] !== null && $result['domain_age_days'] > 365) {
            $score += 0.05;
        }

        if ($result['mailbox_level'] === 'confirmed') {
            $score += 0.05;
        }

        $result['score'] = round(min($score, 1.0), 2);
        
        $result['valid'] = $result['syntax'] && $result['mx'] && ($result['smtp'] || $result['score'] >= 0.35);

        return $result;
    }

    private function validateWithExternalApi(string $email): array
    {
        $fallback = [
            'smtp' => false,
            'mailbox_level' => 'unknown',
            'source' => 'external_api',
            'is_spam_trap' => false,
        ];

        // Try mxcheck.dev (100 free/day)
        try {
            $mxCheckKey = config('services.mxcheck.key');
            
            if ($mxCheckKey) {
                $response = Http::timeout(10)->get("https://mxcheck.dev/api/validate", [
                    'email' => $email,
                ], [
                    'Authorization' => 'Bearer ' . $mxCheckKey,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    
                    if (isset($data['valid'])) {
                        $fallback['smtp'] = $data['valid'];
                        $fallback['mailbox_level'] = $data['valid'] ? 'unconfirmed' : 'unknown';
                        
                        if (isset($data['checks']['disposable'])) {
                            $fallback['is_spam_trap'] = $data['checks']['disposable'];
                        }
                        
                        return $fallback;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::debug('MXCheck API error: ' . $e->getMessage());
        }

        // Try apixies as secondary fallback
        try {
            $apixiesKey = config('services.apixies.key');
            
            if ($apixiesKey) {
                $response = Http::timeout(10)->get("https://api.apixies.com/v1/inspect-email", [
                    'email' => $email,
                ], [
                    'x-api-key' => $apixiesKey,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    
                    if (isset($data['data']['mailbox_exists'])) {
                        $fallback['smtp'] = $data['data']['mailbox_exists'] ?? false;
                        $fallback['mailbox_level'] = $data['data']['mailbox_exists'] ? 'unconfirmed' : 'unknown';
                        
                        if (isset($data['data']['is_disposable'])) {
                            $fallback['is_spam_trap'] = $data['data']['is_disposable'];
                        }
                        
                        return $fallback;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::debug('Apixies API error: ' . $e->getMessage());
        }

        // Conservative fallback for free providers - assume valid
        [, $domain] = explode('@', $email, 2);
        if (in_array(strtolower($domain), self::FREE_PROVIDERS, true)) {
            $fallback['smtp'] = true;
            $fallback['mailbox_level'] = 'unconfirmed';
        }

        return $fallback;
    }

    private function checkSMTP(string $email, array $mxRecords): array
    {
        $smtpResult = [
            'accepted'       => false,
            'is_catch_all'   => false,
            'is_disabled'    => false,
            'mailbox_level'  => 'unknown',
        ];

        $fromDomain = config('app.url') ? parse_url(config('app.url'), PHP_URL_HOST) ?? 'verify.local' : 'verify.local';
        $from = 'verify@' . $fromDomain;

        foreach ($mxRecords as $mx) {
            $host = $mx['target'] ?? null;
            if (!$host) continue;

            $ip = gethostbyname($host);
            if ($ip === $host) continue;

            $connection = @fsockopen($host, 25, $errno, $errstr, 10);
            if (!$connection) continue;

            stream_set_timeout($connection, 10);

            $banner = $this->getResponse($connection);

            if (str_starts_with(trim($banner), '5')) {
                fclose($connection);
                continue;
            }

            $ehloResp = $this->sendCommand($connection, "EHLO {$fromDomain}");
            $supportsStartTls = str_contains(strtoupper($ehloResp), 'STARTTLS');
            $supportsVrfy     = str_contains(strtoupper($ehloResp), 'VRFY');

            if (str_starts_with(trim($ehloResp), '5')) {
                $this->sendCommand($connection, "HELO {$fromDomain}");
            }

            if ($supportsStartTls) {
                $tlsResp = $this->sendCommand($connection, "STARTTLS");

                if (str_starts_with(trim($tlsResp), '220')) {
                    $cryptoOk = @stream_socket_enable_crypto(
                        $connection,
                        true,
                        STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT
                    );

                    if ($cryptoOk) {
                        $this->sendCommand($connection, "EHLO {$fromDomain}");
                    }
                }
            }

            if ($supportsVrfy) {
                $vrfyResp = $this->sendCommand($connection, "VRFY <{$email}>");
                $vrfyCode = (int) substr(trim($vrfyResp), 0, 3);

                if ($vrfyCode === 250 || $vrfyCode === 252) {
                    $smtpResult['mailbox_level'] = 'confirmed';
                }
            }

            $mailFromResp = $this->sendCommand($connection, "MAIL FROM:<{$from}>");

            if (!str_starts_with(trim($mailFromResp), '250')) {
                $this->sendCommand($connection, "QUIT");
                fclose($connection);
                continue;
            }

            $rcptResp = $this->sendCommand($connection, "RCPT TO:<{$email}>");
            $rcptCode = (int) substr(trim($rcptResp), 0, 3);

            if ($rcptCode >= 400 && $rcptCode < 500) {
                $this->sendCommand($connection, "RSET");
                sleep(5);
                $this->sendCommand($connection, "MAIL FROM:<{$from}>");
                $rcptResp = $this->sendCommand($connection, "RCPT TO:<{$email}>");
                $rcptCode = (int) substr(trim($rcptResp), 0, 3);
            }

            $accepted = ($rcptCode === 250 || $rcptCode === 251);

            if ($rcptCode === 550 || $rcptCode === 551 || $rcptCode === 552 || $rcptCode === 553) {
                $smtpResult['is_disabled'] = true;
            }

            if ($accepted) {
                $this->sendCommand($connection, "RSET");
                $this->sendCommand($connection, "MAIL FROM:<{$from}>");

                $randomLocal = 'probe-' . bin2hex(random_bytes(8));
                $randomEmail = $randomLocal . '@' . explode('@', $email)[1];
                $catchResp = $this->sendCommand($connection, "RCPT TO:<{$randomEmail}>");
                $catchCode = (int) substr(trim($catchResp), 0, 3);

                if ($catchCode === 250 || $catchCode === 251) {
                    $smtpResult['is_catch_all'] = true;
                    if ($smtpResult['mailbox_level'] !== 'confirmed') {
                        $smtpResult['mailbox_level'] = 'unconfirmed';
                    }
                } else {
                    $smtpResult['mailbox_level'] = 'confirmed';
                }
            }

            $this->sendCommand($connection, "QUIT");
            fclose($connection);

            $smtpResult['accepted'] = $accepted;
            break;
        }

        return $smtpResult;
    }

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
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        return $response;
    }

    private function estimateDomainAge(string $domain): ?int
    {
        $soa = @dns_get_record($domain, DNS_SOA);

        if (!$soa || empty($soa[0]['serial'])) {
            return null;
        }

        $serial = (string) $soa[0]['serial'];

        if (strlen($serial) >= 8) {
            $dateStr = substr($serial, 0, 8);

            try {
                $soaDate = new \DateTime($dateStr);
                $now     = new \DateTime();

                if ($soaDate < $now) {
                    return (int) $soaDate->diff($now)->days;
                }
            } catch (\Exception) {
                // ignore
            }
        }

        return null;
    }

    private function suggestDomain(string $domain, string $localPart): ?string
    {
        $commonDomains = [
            'gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com',
            'aol.com', 'icloud.com', 'mail.com', 'protonmail.com',
            'zoho.com', 'live.com', 'msn.com', 'yandex.com',
        ];

        $domain = strtolower($domain);

        if (in_array($domain, $commonDomains, true)) {
            return null;
        }

        $bestMatch    = null;
        $bestDistance = PHP_INT_MAX;

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