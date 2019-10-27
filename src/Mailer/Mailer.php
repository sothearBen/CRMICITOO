<?php

namespace App\Mailer;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use App\Entity\User;
use App\Repository\UserRepository;

class Mailer
{
    
    /**
     * @var MailerInterface
     */
    protected $mailer;
    
    /**
     * @var UrlGeneratorInterface
     */
    protected $router;
    
    /**
     * @var EngineInterface
     */
    protected $templating;
    
    /**
     * @var TranslatorInterface
     */
    protected $translator;
    
    /**
     * @var ParameterBagInterface
     */
    protected $parameters;
    
    /**
     * Mailer constructor.
     *
     */
    public function __construct(\Swift_Mailer $mailer, UrlGeneratorInterface $router, EngineInterface $templating, TranslatorInterface $translator, ParameterBagInterface $parameters)
    {
        $this->mailer = $mailer;
        $this->router = $router;
        $this->templating = $templating;
        $this->translator = $translator;
        $this->parameters = $parameters;
    }
    
    public function sendRegistration(User $user)
    {
        $url = $this->router->generate(
            'app_registration_confirm',
            [
                'token' => $user->getConfirmationToken(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $subject = $this->translator->trans('registration.email.subject', [ '%user%' => $user ], 'security');
        $template = 'front/email/register.html.twig';
        $from = [
            $this->parameters->get('configuration.from_email') => $this->parameters->get('configuration.name'),
        ];
        $to = $user->getEmail();
        $body = $this->templating->render($template, [
            'user' => $user,
            'website_name' => $this->parameters->get('configuration.name'),
            'confirmation_url' => $url,
        ]);
        $message = (new \Swift_Message())
            ->setSubject($subject)
            ->setFrom($from)
            ->setTo($to)
            ->setContentType("text/html")
            ->setBody($body);
        $this->mailer->send($message);
    }

    public function sendForgetPassword(User $user)
    {
        $url = $this->router->generate(
            'app_reset_password',
            [
                'token' => $user->getConfirmationToken(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $subject = $this->translator->trans('forget_password.email.subject', [], 'security');
        $template = 'security/email/forget_password.html.twig';
        $from = [
            $this->parameters->get('configuration.from_email') => $this->parameters->get('configuration.name'),
        ];
        $to = $user->getEmail();
        $body = $this->templating->render($template, [
            'user' => $user,
            'website_name' => $this->parameters->get('configuration.name'),
            'confirmation_url' => $url,
        ]);
        $message = (new \Swift_Message())
            ->setSubject($subject)
            ->setFrom($from)
            ->setTo($to)
            ->setContentType("text/html")
            ->setBody($body);
        $this->mailer->send($message);
    }
}
