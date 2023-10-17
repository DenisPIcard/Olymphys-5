<?php
// src/EventListener/ConfigureMenuListener.php

namespace App\EventListener;

use App\Event\ConfigureMenuEvent;

class ConfigureMenuListener
{
    /**
     * @param \App\Event\ConfigureMenuEvent $event
     */
    public function onMenuConfigure(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();

        $menu['Jury']->addChild('Le palmarès', ['route' => 'cyberjury_palmares', 'attributes' => ['class' => 'd-block fas fa-leaf']]);

    }
}


