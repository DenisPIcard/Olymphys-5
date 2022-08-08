<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Repository\JuresRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class UserFixetures extends Fixture
{
    private UserPasswordHasherInterface $passwordEncoder;

    public function __construct(UserPasswordHasherInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $JuresRepository = new JuresRepository;
        $Jures = $JuresRepository->getAll();
        foreach ($jure as $Jures) {

            $user = new User();
            $user->setUsername($jure->getNomJure());
            $user->setRoles(['ROLE_JURE']);
            $user->setEmail($jure->getNomJure() . '@olymp.fr');
            //$user->setPassword($jure->getPrenomJure());
            $user->setPassword($this->passwordEncoder->hashPassword($user, $jure->getPrenomJure()));
            $manager->persist($user);

            // add more products

            $manager->flush();
        }
    }
}

