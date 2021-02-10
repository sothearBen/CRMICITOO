<?php

namespace App\Mailer;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Twig\Mime\NotificationEmail;
use Symfony\Contracts\Translation\TranslatorInterface;

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
    protected $parameterBag;
    
    /**
     * Mailer constructor.
     *
     */
    public function __construct(MailerInterface $mailer, UrlGeneratorInterface $router, Environment $templating, TranslatorInterface $translator, ParameterBagInterface $parameterBag)
    {
        $this->mailer = $mailer;
        $this->router = $router;
        $this->templating = $templating;
        $this->translator = $translator;
        $this->parameterBag = $parameterBag;
    }
    
    public function sendRegistration(User $user, string $locale)
    {
        $url = $this->router->generate(
            'app_registration_confirm',
            [
                '_locale' => $locale,
                'token' => $user->getConfirmationToken(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $email = (new NotificationEmail())
            ->from(new Address(
                $this->parameterBag->get('app.from_email'),
                $this->parameterBag->get('app.name')
            ))
            ->to($user->getEmail())
            ->subject($this->translator->trans('registration.email.subject', [ '%user%' => $user ], 'security'))
            ->htmlTemplate('front/email/register.html.twig')
            ->context([
                'user' => $user,
                'footer_text' => $this->parameterBag->get('app.name'),
                'footer_url' => $this->router->generate(
                    'front_home',
                    [],
                    UrlGeneratorInterface::ABSOLUTE_URL
                )
            ])
            ->action($this->translator->trans('registration.email.action', [], 'security'), $url);
        $this->mailer->send($email);
    }

    public function sendForgetPassword(User $user, string $locale)
    {
        $url = $this->router->generate(
            'app_reset_password',
            [
                '_locale' => $locale,
                'token' => $user->getConfirmationToken(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $email = (new NotificationEmail())
            ->from(new Address(
                $this->parameterBag->get('app.from_email'),
                $this->parameterBag->get('app.name')
            ))
            ->to($user->getEmail())
            ->subject($this->translator->trans('forget_password.email.subject', [], 'security'))
            ->htmlTemplate('security/email/forget_password.html.twig')
            ->context([
                'user' => $user,
                'footer_text' => $this->parameterBag->get('app.name'),
                'footer_url' => $this->router->generate(
                    'front_home',
                    [],
                    UrlGeneratorInterface::ABSOLUTE_URL
                )
            ])
            ->action($this->translator->trans('forget_password.email.action', [], 'security'), $url);
        $this->mailer->send($email);
    }
    
    public function sendResetEmailCheck(User $user, string $newEmail, string $locale)
    {
        $url = $this->router->generate(
            'app_reset_email',
            [
                'token' => $user->getConfirmationToken(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $email = (new NotificationEmail())
            ->from(new Address(
                $this->parameterBag->get('app.from_email'),
                $this->parameterBag->get('app.name')
            ))
            ->to($newEmail)
            ->subject($this->translator->trans('reset_email.email.subject', [], 'security'))
            ->htmlTemplate('security/email/reset_email.html.twig')
            ->context([
                'user' => $user,
                'new_email' => $newEmail,
                'footer_text' => $this->parameterBag->get('app.name'),
                'footer_url' => $this->router->generate(
                    'front_home',
                    [],
                    UrlGeneratorInterface::ABSOLUTE_URL
                )
            ])
            ->action($this->translator->trans('reset_email.email.action', [], 'security'), $url);
        $this->mailer->send($email);
    }

    public function sendInvitation(User $user, string $password, string $locale)
    {
        $url = $this->router->generate(
            'app_registration_confirm',
            [
                'token' => $user->getConfirmationToken(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $email = (new TemplatedEmail())
            ->from(new Address(
                $this->parameterBag->get('configuration')['from_email'],
                $this->parameterBag->get('configuration')['name']
            ))
            ->to($user->getEmail())
            ->subject(
                $this->translator->trans('invitation.email.subject', [
                    '%user%' => $user,
                    '%website_name%' => $this->parameterBag->get('configuration')['name'],
                ], 'back_messages')
            )
            ->htmlTemplate('back/email/invite.html.twig')
            ->context([
                'user' => $user,
                'password' => $password,
                'website_name' => $this->parameterBag->get('configuration')['name'],
                'confirmation_url' => $url,
            ]);
        $this->mailer->send($email);
    }
}