<?php

namespace App\EventListener;

use App\Repository\ConfigRepository;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Twig\Environment;

class GlobalDataListener
{
    private $configRepository;
    private $environment;

    public function __construct(ConfigRepository $configRepository, Environment $environment)
    {
        $this->configRepository = $configRepository;
        $this->environment = $environment;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $this->environment->addGlobal('config', $this->configRepository->findOneByName('app')->get());
    }
}