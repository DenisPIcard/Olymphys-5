<?php

namespace App\Repository;


use App\Entity\Centrescia;
use App\Entity\Docequipes;
use App\Entity\Edition;
use App\Entity\Equipesadmin;
use App\Entity\Orgacia;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use phpDocumentor\Reflection\Types\Collection;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @method Equipesadmin|null find($id, $lockMode = null, $lockVersion = null)
 * @method Equipesadmin|null findOneBy(array $criteria, array $orderBy = null)
 * @method Equipesadmin[]    findAll()
 * @method Equipesadmin[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EquipesadminRepository extends ServiceEntityRepository
{
    private RequestStack $requestStack;
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $registry, RequestStack $requestStack)
    {
        parent::__construct($registry, Equipesadmin::class);
        $this->requestStack = $requestStack;
        $this->doctrine=$registry;
    }


    public function getEquipeInter(Centrescia $centre): array
    {
        $edition=$this->requestStack->getSession()->get('edition');
        return $this->createQueryBuilder('e')->select('e')
            ->andWhere('e.edition =:edition')
            ->setParameter('edition', $edition)
            ->andwhere('e.centre =:centre')
            ->setParameter('centre',$centre)   // on n'affiche que les vraies équipes, pas jury, ambiance, remise des prix qui sont là pour l'affichage des photos
            ->orderBy('e.numero', 'ASC')
            ->getQuery()->getResult();

    }



    public function getEquipeNat() : array
    {
        $edition=$this->requestStack->getSession()->get('edition');
        return $this->createQueryBuilder('e')->select('e')
            ->andWhere('e.edition =:edition')
            ->setParameter('edition', $edition)
            ->andwhere('e.selectionnee= TRUE')
            ->andWhere('e.numero <  100')   // on n'affiche que les vraies équipes, pas jury, ambiance, remise des prix qui sont là pour l'affichage des photos
            ->orderBy('e.lettre', 'ASC')
            ->getQuery()->getResult();


    }

    public function getEquipesProf(User $user): Array
    {
        $edition=$this->requestStack->getSession()->get('edition');
        return $this->createQueryBuilder('e')->select('e')
            ->andWhere('e.edition =:edition')
            ->setParameter('edition', $edition)
            ->andWhere('e.idProf1 = user or e.idProf2 = user')
            ->setParameter('user',$user)
            ->orderBy('e.numero', 'ASC')
            ->getQuery()->getResult();

    }

    public function getEleves(Equipesadmin $equipe): array
    {
        $entityManager = $this->doctrine->getManager();

        $query = $entityManager->createQuery(
            'SELECT e
            FROM App\Entity\Elevesinter e
            WHERE e.equipe =:equipe
            ORDER BY e.nom ASC'
        )->setParameter('equipe', $equipe);

        // returns an array of Product objects
        return $query->getResult();


    }

    public function getEquipes_prof_cn(User $prof, Edition $edition): array
    {
        $entityManager = $this->getEntityManager();
        $edition=$this->doctrine->getRepository(Edition::class)->find($edition->getId());

        $query = $entityManager->createQuery(
            'SELECT e
            FROM App\Entity\Equipesadmin e 
            WHERE (e.idProf1 =:prof1 OR e.idProf2 =:prof2) AND e.selectionnee = TRUE AND e.edition =:edition
            ORDER BY e.lettre ASC')
            ->setParameter('prof1', $prof)
            ->setParameter('prof2', $prof)
            ->setParameter('edition', $edition);
        return $query->execute();


    }

    public function getNumeros(): string
    {//donne la liste des N° des équipes du professeur de l'édition listée

        $em = $this->getEntityManager();

        $qb = $em->getRepository(Equipesadmin::class)->createQueryBuilder('e')
            ->where('e.idProf1 =: prof1 or e.idprof2 =:prof')
            ->andWhere('e.edition =:edition')
            ->setParameters(['edition' => $this->edition, 'prof1' => $this->idProf1, 'prof2' => $this->idProf2]);
        $listeEquipes = $qb->getQuery()->getResult();
        $numero = $this->getNumero();
        foreach ($listeEquipes as $equipe) {
            if ($equipe != $this)
                $numeros = $numeros . '-' . $equipe->getNumero();

        }
        return $numeros;
    }
    public function getListeEquipe($user,$concours,$choix,$centre)
    {
        $em = $this->getEntityManager();
        $editionN=$this->requestStack->getSession()->get('edition');
        $editionN1= $this->doctrine->getRepository(Edition::class)->findOneBy(['ed'=>$editionN->getEd()-1]);
        $concours == 'interacadémique'?$selectionnee=false:$selectionnee=true;
        $qb = $em->getRepository(Equipesadmin::class)->createQueryBuilder('e');
        new DateTime('now')>=$this->requestStack->getSession()->get('dateouverturesite')? $editionAff=$editionN: $editionAff=$editionN1;
        $qb->andWhere('e.edition =:edition');
        $concours == 'interacadémique'?$qb->orderBy('e.numero', 'ASC'):$qb->orderBy('e.lettre', 'ASC');

        if (in_array('ROLE_PROF',$user->getRoles()) and (!in_array('ROLE_JURY',$user->getRoles()))) {// à cause du juré qui est prof et juré selon les années
            $qb ->andWhere('e.idProf1 =:prof or e.idProf2 =:prof')
                ->andWhere('e.selectionnee = :selectionnee')
                ->setParameters(['edition' => $editionAff, 'prof' => $user, 'selectionnee' => $selectionnee]);
            $listeEquipes= $qb->getQuery()->getResult();
        }
        if ( (in_array('ROLE_JURY',$user->getRoles())) or (in_array('ROLE_JURYCIA',$user->getRoles())) or (in_array('ROLE_COMITE',$user->getRoles())) or (in_array('ROLE_ORGACIA',$user->getRoles()))or(in_array('ROLE_SUPER_ADMIN',$user->getRoles()))){
            if ($centre!=null) {
                $listeEquipes=$this->getEquipeInter($centre);
            }
            if ($choix=='liste_cn_comite') {
                $qb ->andWhere('e.numero <:valeur')
                    ->andWhere('e.selectionnee = 1')
                    ->setParameters(['edition' => $editionAff, 'valeur' => 100]);
                $listeEquipes= $qb->getQuery()->getResult();
            }

        }
       return $listeEquipes;
    }
}