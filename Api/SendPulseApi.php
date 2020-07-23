<?php

namespace Creonit\SendPulseMailer\Api;

use Symfony\Component\Mailer\Exception\HttpTransportException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class SendPulseApi
{
    /**
     * @var HttpClientInterface
     */
    protected $client;

    /**
     * @var string
     */
    protected $host;

    /**
     * @var string
     */
    protected $userId;
    /**
     * @var string
     */
    protected $secret;

    /**
     * @var string|null
     */
    protected $token;

    /**
     * @var string
     */
    protected $tokenHash;

    /**
     * @var TokenStorage
     */
    protected $tokenStorage;

    public function __construct(HttpClientInterface $client, string $userId, string $secret, string $host = 'api.sendpulse.com')
    {
        $this->host = $host;
        $this->userId = $userId;
        $this->secret = $secret;

        $this->client = $client;

        $this->tokenStorage = new TokenStorage();
        $this->tokenHash = md5("{$userId}:{$secret}");

        $this->token = $this->tokenStorage->get($this->tokenHash);

        if (empty($this->getToken())) {
            throw new \Exception('Could not connect to api, check your ID and SECRET');
        }
    }

    protected function getToken()
    {
        if (null === $this->token) {
            $this->token = $this->refreshToken();
        }

        return $this->token;
    }

    public function request(string $method, string $endpoint, array $options = [], bool $secure = true): ResponseInterface
    {
        if ($secure) {
            $this->injectAuthOption($options);
        }

        $response = $this->client->request($method, $this->buildUrl($endpoint), $options);

        if (200 !== $response->getStatusCode()) {
            $result = $response->toArray(false);
            throw new HttpTransportException(sprintf('Unable to send an email [%s (%d)].', $result['message'], $result['error_code'] ?? -1), $response);
        }

        return $response;
    }

    protected function injectAuthOption(array &$options)
    {
        if (!isset($options['headers'])) {
            $options['headers'] = [];
        }

        $options['headers']['Authorization'] = "Bearer {$this->getToken()}";
    }

    protected function buildUrl(string $endpoint)
    {
        $url = join('/', [
            $this->host,
            $endpoint,
        ]);

        $url = preg_replace_callback('/^((http[s]?):\/\/)?/', function($match) {
            return $match[0] ?: 'https://';
        }, $url);

        return $url;
    }

    protected function refreshToken()
    {
        $data = [
            'grant_type' => 'client_credentials',
            'client_id' => $this->userId,
            'client_secret' => $this->secret,
        ];

        $response = $this->request('POST', 'oauth/access_token', ['json' => $data], false);
        $result = $response->toArray(false);
        $result['expired_time'] = time() + $result['expires_in'];

        $token = $result['access_token'];

        $this->tokenStorage->set($this->tokenHash, json_encode($result));

        return $token;
    }
}
