<?php

namespace Creonit\SendPulseMailer\Transport;

use Symfony\Component\Mailer\Transport\AbstractTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportInterface;

final class SendPulseTransportFactory extends AbstractTransportFactory
{
    public function create(Dsn $dsn): TransportInterface
    {
        $user = $this->getUser($dsn);
        $password = $this->getPassword($dsn);

        $host = 'default' === $dsn->getHost() ? null : $dsn->getHost();
        $port = $dsn->getPort();

        switch ($this->getTransportType($dsn)) {
            case 'events':
                $transport = (new SendPulseEventsApiTransport($user, $password))->setHost($host)->setPort($port);
                break;

            case 'smtp-api':
                $transport = (new SendPulseSmtpApiTransport($user, $password))->setHost($host)->setPort($port);
                break;

            case 'smtp':
            default:
                $host = 'default' === $dsn->getHost() ? 'smtp-pulse.com' : $dsn->getHost();
                $port = $port ?: 587;

                $transport = new SendPulseSmtpTransport($user, $password, $host, $port);
                break;
        }

        return $transport;
    }

    protected function getTransportType(Dsn $dsn)
    {
        preg_match('/sendpulse\+?(.*)/', $dsn->getScheme(), $matches);

        return $matches[1] ?: 'default';
    }

    protected function getSupportedSchemes(): array
    {
        return ['sendpulse', 'sendpulse+smtp', 'sendpulse+smtp-api', 'sendpulse+events',];
    }
}