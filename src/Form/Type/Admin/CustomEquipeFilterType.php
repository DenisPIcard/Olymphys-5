<?php


namespace App\Form\Type\Admin;

use App\Entity\Equipesadmin;

use App\Entity\Odpf\OdpfEditionsPassees;
use App\Entity\Odpf\OdpfEquipesPassees;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

//use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


class CustomEquipeFilterType extends AbstractType
{

    private EntityManagerInterface $doctrine;
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack, EntityManagerInterface $doctrine)
    {
        $this->requestStack = $requestStack;
        $this->doctrine = $doctrine;
    }

    public function configureOptions(OptionsResolver $resolver)

    {
        $edition = $this->requestStack->getSession()->get('edition');
        $listeEquipes = $this->doctrine->getRepository(Equipesadmin::class)->createQueryBuilder('e')
            ->andWhere('e.edition=:edition')
            ->setParameter('edition', $edition)
            ->addOrderBy('e.numero', 'ASC')
            ->getQuery()->getResult();

        $resolver->setDefaults([
            'comparison_type_options' => ['type' => 'entity'],
            'value_type' => EntityType::class,
            'class' => Equipesadmin::class,
            'choices' => $listeEquipes
        ]);

    }

    public function getParent(): string
    {
        return EntityType::class;
    }


}
