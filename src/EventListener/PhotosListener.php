<?php

namespace App\EventListener;


use App\Entity\Photos;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class PhotosListener
{
    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        // if this listener only applies to certain entity types,
        // add some code to check the entity type as early as possible
        if (!$entity instanceof Photos) {
            return;
        }
        $entity->createThumbs();

    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        // if this listener only applies to certain entity types,
        // add some code to check the entity type as early as possible
        if (!$entity instanceof Photos) {
            return;
        }
        $entity->createThumbs();

    }
}
