<?php

namespace App\Repository;

use App\Entity\Centrescia;
use App\Entity\Classement;

/**
 * @method Classement|null find($id, $lockMode = null, $lockVersion = null)
 * @method Classement|null findOneBy(array $criteria, array $orderBy = null)
 * @method Classement[]    findAll()
 * @method Classement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClassementRepository extends \Doctrine\ORM\EntityRepository
{
}
