<?php


namespace App\Form\Type\Admin;

use App\Entity\Elevesinter;
use App\Entity\Equipesadmin;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

//use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


class CustomEquipeSelectionnesFilterType extends AbstractType
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


        $resolver->setDefaults([
            'comparison_type_options' => ['type' => 'choice'],

            'choices' => ['non' => false, 'oui' => true]
        ]);

    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }


}
