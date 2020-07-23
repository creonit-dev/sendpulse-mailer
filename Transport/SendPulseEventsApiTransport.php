<?php

namespace Creonit\SendPulseMailer\Transport;

use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Exception\HttpTransportException;
use Symfony\Component\Mailer\Exception\InvalidArgumentException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\HttpClient\ResponseInterface;

class SendPulseEventsApiTransport extends AbstractSendPulseApiTransport
{
    protected const HOST = 'events.sendpulse.com';

    protected function doSendApi(SentMessage $sentMessage, Email $email, Envelope $envelope): ResponseInterface
    {
        if (!$eventHeader = $email->getHeaders()->get('X-SendPulse-Event')) {
            throw new InvalidArgumentException("X-SendPulse-Event header undefined");
        }

        $eventName = $eventHeader->getBody();

        $response = $this->api->request('POST', "events/name/{$eventName}", ['json' => $this->getEmailData($email, $envelope)]);

        $result = $response->toArray(false);

        if (false === ($result['result'] ?? false)) {
            throw new HttpTransportException(sprintf('Unable to send an email [%s].', $result['message']), $response);
        }

        $sentMessage->setMessageId(uniqid());

        return $response;
    }

    protected function getEmailData(Email $email, Envelope $envelope)
    {
        if (!$to = $this->getFirstRecipient($email, $envelope)) {
            throw new InvalidArgumentException('Email is empty');
        }

        return array_merge([
            'email'=> $to->getAddress(),
            'micro_time' => microtime(),
        ], $this->getVariables($email));
    }

    protected function getType(): string
    {
        return 'events';
    }
}