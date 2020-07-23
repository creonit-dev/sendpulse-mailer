<?php

namespace Creonit\SendPulseMailer\Transport;

use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class SendPulseSmtpTransport extends EsmtpTransport
{
    public function __construct(string $username, string $password, string $host, int $port = 0, bool $tls = null, EventDispatcherInterface $dispatcher = null, LoggerInterface $logger = null)
    {
        parent::__construct('smtp-pulse.com', $port, $tls, $dispatcher, $logger);

        $this->setUsername($username);
        $this->setPassword($password);
    }
}