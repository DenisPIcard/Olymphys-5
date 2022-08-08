<?php

namespace App\EventListener;


use App\Entity\Odpf\OdpfImagescarousels;

use Doctrine\Persistence\Event\LifecycleEventArgs;

class ImagesCarouselsListener
{
    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        // if this listener only applies to certain entity types,
        // add some code to check the entity type as early as possible
        if (!$entity instanceof OdpfImagescarousels) {
            return;
        }
        $entity->createThumbs();

    }
}
