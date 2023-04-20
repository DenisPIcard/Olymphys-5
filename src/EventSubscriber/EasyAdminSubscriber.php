<?php
//source : https://grafikart.fr/forum/33951
namespace App\EventSubscriber;

use App\Entity\Fichiersequipes;
use App\Entity\Odpf\OdpfFichierspasses;
use App\Entity\Photos;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class EasyAdminSubscriber implements EventSubscriberInterface
{

    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordEncoder;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordEncoder)
    {
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityPersistedEvent::class => ['addUser'],
            BeforeEntityUpdatedEvent::class => ['updateUser'], //surtout utile lors d'un reset de mot passe plutôt qu'un réel update, car l'update va de nouveau encrypter le mot de passe DEJA encrypté ...
            AfterEntityPersistedEvent::class => ['traitement'],
            AfterEntityUpdatedEvent::class => ['updateEntity']

        ];
    }

    public function updateUser(BeforeEntityUpdatedEvent $event)
    {
        $entity = $event->getEntityInstance();

        if (!($entity instanceof User)) {
            return;
        }
        $this->setPassword($entity);
    }

    public function addUser(BeforeEntityPersistedEvent $event)
    {
        $entity = $event->getEntityInstance();

        if (!($entity instanceof User)) {
            return;
        }
        $entity = $event->getEntityInstance();

        if (!($entity instanceof User)) {
            return;
        }
        $entity->setCreatedAt(new  DateTime('now'));
        $entity->setLastVisit(new  DateTime('now'));//Pour que le nouvel user puisse se connecter sans avoir une demande de confirmation de l'adresse mail
        $this->setPassword($entity);
    }


    /**
     * @param User $entity
     */
    public function setPassword(User $entity): void
    {

        if ($entity instanceof User) {
            return;
        }
        $pass = $entity->getPassword();

        $entity->setPassword(
            $this->passwordEncoder->hashPassword(
                $entity,
                $pass
            )
        );

        $entity->setUpdatedAt(new  DateTime('now'));


        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    public function Traitement(AfterEntityPersistedEvent $event)
    {
        $entity = $event->getEntityInstance();

        if ($entity instanceof Photos) {
            $entity->createThumbs();
        }
        if ($entity instanceof OdpfFichierspasses) {

            if ($entity->getTypefichier() < 4) {

                $entity->moveFile();

            }
        }

        return;
    }

    /**
     * @throws \ImagickException
     */
    public function updateEntity(AfterEntityUpdatedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        if ($entity instanceof Photos) {
            $entity->createThumbs();
        }
        /*if ($entity instanceof OdpfFichierspasses) {

            if ($entity->getTypefichier() < 4) {

                $entity->moveFile();
                return;
            }
        }*/
        return;


    }

    public function setAutorisation(AfterEntityPersistedEvent $event)
    {
        $entity = $event->getEntityInstance();

        if (!($entity instanceof Fichiersequipes)) {
            return;
        }
        if ($entity->getTypefichier() == 6) {
            $citoyen = $entity->getProf();
            if (null == $citoyen) {
                $citoyen = $entity->getEleve();
            }
            $citoyen->setAutorisationphotos($entity);
            $this->entityManager->persist($citoyen);
            $this->entityManager->flush();

        }

    }

}