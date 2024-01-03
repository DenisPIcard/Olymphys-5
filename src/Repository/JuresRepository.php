<?php

namespace App\Repository;


use App\Entity\Equipes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Entity\Jures;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @extends ServiceEntityRepository<Jures>
 *
 * @method Jures|null find($id, $lockMode = null, $lockVersion = null)
 * @method Jures|null findOneBy(array $criteria, array $orderBy = null)
 * @method Jures[]    findAll()
 * @method Jures[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JuresRepository extends ServiceEntityRepository
{
    private RequestStack $requestStack;
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $registry, RequestStack $requestStack)
    {
        parent::__construct($registry, Jures::class);
        $this->requestStack = $requestStack;
        $this->doctrine = $registry;
    }

    public function getAttributionAdmin($jure): array
    {
        $attributionsJure = $jure->getAttributions();

        $attribution = [];

        foreach ($attributionsJure as $attributionJure) {
            if ($attributionJure->getEquipe() != null) {
                if ($attributionJure->GetEstLecteur() == 1) {
                    $valeur = 1;
                }
                if ($attributionJure->GetEstLecteur() == 0) {
                    $valeur = 0;
                }
                if ($attributionJure->GetEstLecteur() == 2) {
                    $valeur = 2;
                }
                if ($attributionJure->GetEstLecteur() === null) {
                    $valeur = '_';
                }
                $attribution[$attributionJure->getEquipe()->getEquipeinter()->getLettre()] = $valeur;
            }
        }
        return $attribution;

    }

    public function getAttribution($jure): array
    {
        $attributionsJure = $jure->getAttributions();

        $attribution = [];

        foreach ($attributionsJure as $attributionJure) {
            if ($attributionJure->GetEstLecteur() !== null) {

                $attribution[$attributionJure->getEquipe()->getEquipeinter()->getLettre()] = $attributionJure->GetEstLecteur();
            }
        }
        return $attribution;

    }
}


