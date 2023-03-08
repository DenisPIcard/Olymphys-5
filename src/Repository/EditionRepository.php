<?php

namespace App\Repository;

use App\Entity\Edition;
use DateInterval;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @method Edition|null find($id, $lockMode = null, $lockVersion = null)
 * @method Edition|null findOneBy(array $criteria, array $orderBy = null)
 * @method Edition[]    findAll()
 * @method Edition[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EditionRepository extends ServiceEntityRepository

{
    private RequestStack $requestStack;

    public function __construct(ManagerRegistry $registry, RequestStack $requestStack)
    {
        $this->requestStack=$requestStack;
        parent::__construct($registry, Edition::class);
    }


    public function getEdition(EditionRepository $er): QueryBuilder
    {

        return $er->createQueryBuilder('e')->select('e');

    }

    public function getLastEdition(EditionRepository $er): QueryBuilder
    {
        $edition = $er->findOneBy([], ['id' => 'desc']);
        $lastid = $edition->getId();
        return $er->createQueryBuilder('e')->select('e')
            ->where('e.id=:lastid')
            ->setParameter('lastid', $lastid);

    }

    /**
     * @throws \Exception
     */
    public function setDates($edition)
    {
        $datelimphotoscia = date_create();
        $datelimphotoscn = date_create();
        $datelimdiaporama = new DateTime($this->requestStack->getSession()->get('edition')->getConcourscn()->format('Y-m-d'));
        $p = new DateInterval('P7D');
        $datelimlivredor = new DateTime($this->requestStack->getSession()->get('edition')->getConcourscn()->format('Y-m-d'));

        $datelivredor = new DateTime($this->requestStack->getSession()->get('edition')->getConcourscn()->format('Y-m-d') . '00:00:00');
        $datelimlivredoreleve = new DateTime($this->requestStack->getSession()->get('edition')->getConcourscn()->format('Y-m-d') . '18:00:00');
        date_date_set($datelimphotoscia, $edition->getconcourscia()->format('Y'), $edition->getconcourscia()->format('m'), $edition->getconcourscia()->format('d') + 30);
        date_date_set($datelimphotoscn, $edition->getconcourscn()->format('Y'), $edition->getconcourscn()->format('m'), $edition->getconcourscn()->format('d') + 30);
        date_date_set($datelivredor, $edition->getconcourscn()->format('Y'), $edition->getconcourscn()->format('m'), $edition->getconcourscn()->format('d') - 1);
        date_date_set($datelimdiaporama, $edition->getconcourscn()->format('Y'), $edition->getconcourscn()->format('m'), $edition->getconcourscn()->format('d') - 7);
        date_date_set($datelimlivredor, $edition->getconcourscn()->format('Y'), $edition->getconcourscn()->format('m'), $edition->getconcourscn()->format('d') + 8);
        $this->requestStack->getSession()->set('datelimphotoscia', $datelimphotoscia);
        $this->requestStack->getSession()->set('datelimphotoscn', $datelimphotoscn);
        $this->requestStack->getSession()->set('datelivredor', $datelivredor);
        $this->requestStack->getSession()->set('datelimlivredor', $datelimlivredor);
        $this->requestStack->getSession()->set('datelimlivredoreleve', $datelimlivredoreleve);
        $this->requestStack->getSession()->set('datelimdiaporama', $datelimdiaporama);
        $this->requestStack->getSession()->set('dateclotureinscription', new DateTime($this->requestStack->getSession()->get('edition')->getConcourscn()->format('Y-m-d H:i:s')));
        $this->requestStack->getSession()->set('dateouverturesite', new DateTime($this->requestStack->getSession()->get('edition')->getDateouverturesite()->format('Y-m-d H:i:s')));

    }

}
