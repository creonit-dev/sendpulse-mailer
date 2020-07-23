<?php

namespace Creonit\SendPulseMailer\Transport;

use Creonit\SendPulseMailer\Api\SendPulseApi;
use Creonit\SendPulseMailer\Header\SendPulseVariableHeader;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Transport\AbstractApiTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class AbstractSendPulseApiTransport extends AbstractApiTransport
{
    protected const HOST = 'api.sendpulse.com';
    protected $userId;
    protected $secret;

    /**
     * @var SendPulseApi
     */
    protected $api;

    public function __construct(string $userId, string $secret, HttpClientInterface $client = null, EventDispatcherInterface $dispatcher = null, LoggerInterface $logger = null)
    {
        parent::__construct($client, $dispatcher, $logger);
        $this->userId = $userId;
        $this->secret = $secret;

        $this->api = new SendPulseApi($this->client, $userId, $secret, $this->getHost());
    }

    protected function getBaseUrl()
    {
        return $this->getHost() . ($this->port ? ":{$this->port}" : '');
    }

    protected function getHost()
    {
        return $this->host ?: static::HOST;
    }

    public function __toString(): string
    {
        return sprintf('sendpulse%s://%s', $this->getType(), $this->getBaseUrl());
    }


    protected function getFirstRecipient(Email $email, Envelope $envelope): ?Address
    {
        $recipients = $this->getRecipients($email, $envelope);

        return $recipients[0] ?? null;
    }

    /**
     * @param Email $email
     *
     * @return SendPulseVariableHeader[]
     */
    protected function getVariableHeaders(Email $email)
    {
        $headers = [];

        foreach ($email->getHeaders()->all() as $header) {
            if ($header instanceof SendPulseVariableHeader) {
                $headers[] = $header;
            }
        }

        return $headers;
    }

    protected function getVariables(Email $email)
    {
        $variables = [];
        $headers = $this->getVariableHeaders($email);

        foreach ($headers as $header) {
            $variables[$header->getKey()] = $header->getBodyAsString();
        }

        return $variables;
    }

    abstract protected function getType(): string;
}