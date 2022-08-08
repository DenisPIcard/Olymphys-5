<?php
// src/EventSubscriber/ExceptionSubscriber.php
namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;


class ExceptionSubscriber implements EventSubscriberInterface


{
    private $twig;

    public function __construct(Environment $environment)
    {

        $this->twig = $environment;
    }


    public static function getSubscribedEvents(): array
    {
        // return the subscribed events, their methods and priorities
        return [
            KernelEvents::EXCEPTION => [
                ['processException', 10],
                ['logException', 0],
                ['notifyException', -10],
            ],
        ];
    }

    public function processException(ExceptionEvent $event)
    {


    }

    public function logException(ExceptionEvent $event)
    {

    }

    public function notifyException(ExceptionEvent $event)
    {
        //return new RedirectResponse($this->urlGenerator->generate('https://www.olymphys.fr'));
    }
}