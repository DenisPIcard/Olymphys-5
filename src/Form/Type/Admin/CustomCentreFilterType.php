<?php

namespace App\Form\Type\Admin;

use App\Entity\Centrescia;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

//use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


class CustomCentreFilterType extends AbstractType
{
    private $requestStack;
    private EntityManagerInterface $doctrine;

    public function __construct(RequestStack $requestStack, EntityManagerInterface $doctrine)
    {
        $this->requestStack = $requestStack;
        $this->doctrine=$doctrine;
    }


    public function configureOptions(OptionsResolver $resolver)

    {
        $listeCentres=$this->doctrine->getRepository(Centrescia::class)->findBy(['actif'=>true]);

        $resolver->setDefaults([
            'comparison_type_options' => ['type' => 'entity'],
            'value_type' => EntityType::class,
            'class' => Centrescia::class,
            'choices'=>$listeCentres
            ]);

    }

    public function getParent(): string
    {
        return EntityType::class;
    }


}