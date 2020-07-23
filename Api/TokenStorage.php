<?php

namespace Creonit\SendPulseMailer\Api;


class TokenStorage
{
    /**
     * @var string
     */
    protected $path;

    public function __construct(string $path = '')
    {
        $this->path = $path;
    }

    public function set(string $key, string $token)
    {
        $tokenFile = fopen($this->path . $key, 'wb');
        fwrite($tokenFile, $token);
        fclose($tokenFile);
    }

    public function get(string $key)
    {
        $filePath = $this->path . $key;
        if (file_exists($filePath)) {
            $data = file_get_contents($filePath);
            if (!$decoded = @json_decode($data, true)) {
                return null;
            }

            return $this->getActiveToken($decoded);
        }

        return null;
    }

    public function delete(string $key)
    {
        $filePath = $this->path . $key;
        if (file_exists($filePath)) {
            return unlink($filePath);
        }

        return false;
    }

    protected function getActiveToken(array $tokenData)
    {
        $token = $tokenData['access_token'] ?? null;
        $expiredTime = $tokenData['expired_time'] ?? time();

        return $expiredTime > time() ? $token : null;
    }
}