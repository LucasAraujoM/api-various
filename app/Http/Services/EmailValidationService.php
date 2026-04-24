<?php

namespace App\Http\Services;

class EmailValidationService
{
    public function validate(string $email): array
    {
        $result = [
            'email' => $email,
            'format' => false,
            'mx' => false,
            'smtp' => false,
            'score' => 0,
        ];

        // 1. Validar formato
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $result;
        }

        $result['format'] = true;

        // 2. Obtener dominio
        $domain = substr(strrchr($email, "@"), 1);

        // 3. Chequear MX records
        $mxRecords = dns_get_record($domain, DNS_MX);

        if (!$mxRecords || count($mxRecords) === 0) {
            return $result;
        }

        $result['mx'] = true;

        // 4. SMTP Check (básico)
        $smtpValid = $this->checkSMTP($email, $mxRecords);

        $result['smtp'] = $smtpValid;

        // 5. Score simple
        $score = 0;
        if ($result['format'])
            $score += 0.2;
        if ($result['mx'])
            $score += 0.3;
        if ($result['smtp'])
            $score += 0.5;

        $result['score'] = $score;

        return $result;
    }

    private function checkSMTP(string $email, array $mxRecords): bool
    {
        $from = 'test@tuapp.com';

        foreach ($mxRecords as $mx) {
            $host = $mx['target'];

            $connection = @fsockopen($host, 25, $errno, $errstr, 5);

            if (!$connection) {
                continue;
            }

            stream_set_timeout($connection, 5);

            $this->getResponse($connection); // banner

            $this->sendCommand($connection, "HELO tuapp.com");
            $this->sendCommand($connection, "MAIL FROM:<$from>");
            $response = $this->sendCommand($connection, "RCPT TO:<$email>");

            fclose($connection);

            if (str_starts_with($response, '250')) {
                return true;
            }
        }

        return false;
    }

    private function sendCommand($connection, string $command): string
    {
        fwrite($connection, $command . "\r\n");
        return $this->getResponse($connection);
    }

    private function getResponse($connection): string
    {
        $response = '';

        while ($line = fgets($connection, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) == ' ')
                break;
        }

        return $response;
    }
}
