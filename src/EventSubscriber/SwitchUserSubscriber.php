<?php
// src/EventListener/SwitchUserSubscriber.php
namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class SwitchUserSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            // constant for security.switch_user
            SecurityEvents::SWITCH_USER => 'onSwitchUser',
        ];
    }

    public function onSwitchUser(SwitchUserEvent $event)
    {
        $request = $event->getRequest();

        if ($request->hasSession() && ($session = $request->getSession())) {
            $session->set(
                '_username',
                // assuming your User has some getLocale() method
                $event->getTargetUser()->getUsername()
            );
        }
    }
}