<?php

namespace App\Repository\Odpf;

use App\Entity\Odpf\OdpfPartenaires;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @method OdpfPartenaires|null find($id, $lockMode = null, $lockVersion = null)
 * @method OdpfPartenaires|null findOneBy(array $criteria, array $orderBy = null)
 * @method OdpfPartenaires[]    findAll()
 * @method OdpfPartenaires[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OdpfPartenairesRepository extends ServiceEntityRepository
{
    private RequestStack $requestStack;

    public function __construct(ManagerRegistry $registry, RequestStack $requestStack)
    {
        parent::__construct($registry, OdpfPartenaires::class);
        $this->requestStack = $requestStack;
    }

    public function textespartenaires()
    {
        return $this->createQueryBuilder('e')
            ->select('e')
            ->orderBy('e.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}