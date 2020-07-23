<?php

namespace Creonit\SendPulseMailer\Transport;

use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\HttpClient\ResponseInterface;

class SendPulseSmtpApiTransport extends AbstractSendPulseApiTransport
{
    protected function doSendApi(SentMessage $sentMessage, Email $email, Envelope $envelope): ResponseInterface
    {
        $response = $this->api->request('POST', 'smtp/emails', [
            'json' => $this->getEmailData($email, $envelope),
        ]);

        $result = $response->toArray(false);

        $sentMessage->setMessageId($result['id']);

        return $response;
    }

    protected function getEmailData(Email $email, Envelope $envelope)
    {
        return [
            'html'=> base64_encode($email->getHtmlBody()),
            'text'=> $email->getTextBody(),
            'subject'=> $email->getSubject(),
            'from'=> [
                'email' => $envelope->getSender()->getAddress(),
                'name'=> $envelope->getSender()->getName(),
            ],
            'to'=> $this->getRecipients($email, $envelope),
        ];
    }

    protected function getRecipients(Email $email, Envelope $envelope): array
    {
        return array_map(function(Address $recipient) {
            return [
                'email' => $recipient->getAddress(),
                'name'=> $recipient->getName(),
            ];
        }, $envelope->getRecipients());
    }

    protected function getType(): string
    {
        return 'smtp-api';
    }
}