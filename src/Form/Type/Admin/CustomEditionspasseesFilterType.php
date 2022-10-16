<?php


namespace App\Form\Type\Admin;

use App\Entity\Odpf\OdpfEditionsPassees;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

//use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


class CustomEditionspasseesFilterType extends AbstractType
{
    private $requestStack;
    private EntityManager $doctrine;

    public function __construct(RequestStack $requestStack, EntityManagerInterface $doctrine)
    {
        $this->requestStack = $requestStack;
        $this->doctrine = $doctrine;
    }


    public function configureOptions(OptionsResolver $resolver)

    {
        $listeEditPass = $this->doctrine->getRepository(OdpfEditionsPassees::class)->createQueryBuilder('e')
            ->addOrderBy('e.edition', 'DESC')
            ->getQuery()->getResult();
        $resolver->setDefaults([
            'comparison_type_options' => ['type' => 'entity'],
            'value_type' => EntityType::class,
            'class' => OdpfEditionsPassees::class,
            'choices' => $listeEditPass
        ]);

    }

    public function getParent(): string
    {
        return EntityType::class;
    }


}
