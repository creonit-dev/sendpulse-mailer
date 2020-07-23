SendPulse Mailer
================

[SendPulse](https://sendpulse.com/)

[Symfony Mailer Documentation](https://symfony.com/doc/current/mailer.html)


Supported schemes
------------------
`smtp`
```dotenv
MAILER_DSN=sendpulse://USERNAME:PASSWORD@default
```

`smtp api`
```dotenv
MAILER_DSN=sendpulse+smtp-api://USER_ID:SECRET@default
```

`Automation360 events`
```dotenv
MAILER_DSN=sendpulse+events://USER_ID:SECRET@default
```

```php
use Creonit\SendPulseMailer\Header\SendPulseVariableHeader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailingController extends AbstractController
{
    public function sendMail(MailerInterface $mailer)
    {
        $to = 'example@example.com';
        $message = new Email();
        $message->to($to);
        $message->getHeaders()
            ->addTextHeader('X-SendPulse-Event', 'event_name')
            ->add(new SendPulseVariableHeader('variable_name', 'value'));
        
        $mailer->send($message);
    }
}
```
