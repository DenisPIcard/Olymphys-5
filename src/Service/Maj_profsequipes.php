<?php

namespace App\Service;

use App\Entity\Equipesadmin;
use App\Entity\Professeurs;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;


class Maj_profsequipes
{


    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;

    }

    public function maj_profsequipes($equipe)
    {
        $em = $this->doctrine->getManager();
        //$em=$this->getDoctrine()->getManager();

        $repositoryProfesseurs = $this->doctrine->getRepository(Professeurs::class);
        $repositoryUser = $this->doctrine->getRepository(User::class);
        $repositoryEquipesadmin = $em->getRepository(Equipesadmin::class);
        $prof1 = $repositoryUser->findOneBy(['id' => $equipe->getIdProf1()->getId()]);
        $profuser1 = $repositoryProfesseurs->findOneBy(['user' => $prof1]);
        if (is_null($profuser1)) {
            $profuser1 = new Professeurs();
            $profuser1->setUser($prof1);
            $em->persist($profuser1);
            $em->flush();
        }
        if ($equipe->getIdProf2() != null) {
            $prof2 = $repositoryUser->findOneBy(['id' => $equipe->getIdProf2()->getId()]);
            $profuser2 = $repositoryProfesseurs->findOneBy(['user' => $prof2]);
            if (is_null($profuser2)) {
                $profuser2 = new Professeurs();
                $profuser2->setUser($prof2);
                $em->persist($profuser2);
                $em->flush();
            }
        }
        $equipe = $repositoryEquipesadmin->createQueryBuilder('e')
            ->where('e.id =:id')
            ->setParameter('id', $equipe->getId())
            ->getQuery()->getSingleResult();
        $profuser1->addEquipe($equipe);
        $profuser1->setEquipesString($equipe->getEdition()->getEd() . ':' . $equipe->getNumero());
        $em->persist($profuser1);
        $em->flush();
        if ($equipe->getIdProf2() != null) {
            $profuser2 = $repositoryProfesseurs->findOneBy(['user' => $equipe->getIdProf2()]);
            $equipes = $profuser2->getEquipes()->getValues();
            $profuser2->addEquipe($equipe);
            $profuser2->setEquipesString($equipe->getEdition()->getEd() . ':' . $equipe->getNumero());
            $em->persist($profuser2);
            $em->flush();
        }
        //En cas de supression ou changement de prof1 ou 2

        $listeprofs = $repositoryProfesseurs->findAll();

        foreach ($listeprofs as $prof) {
            $equipesprof = $prof->getEquipes()->getvalues();

            if (in_array($equipe, $equipesprof, true)) {
                if ($prof->getUser() != $equipe->getIdProf1()) {

                    if ($prof->getUser() != $equipe->getIdProf2()) {

                        $prof->removeEquipe($equipe);
                        $em->persist($prof);
                        $em->flush();
                    }
                }
            }
        }
        $em->flush();
    }

}