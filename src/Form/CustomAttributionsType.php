<?php

namespace App\Form;

use App\Entity\Attributions;
use App\Entity\Equipes;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomAttributionsType extends AbstractType
{
    private ManagerRegistry $doctrine;
    private RequestStack $requestStack;


    public function __construct(ManagerRegistry $doctrine, RequestStack $requestStack)
    {
        $this->doctrine = $doctrine;
        $this->requestStack = $requestStack;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $options['page'] == 'edit' ? $disabled = true : $disabled = false;
        $builder
            ->add('equipe', EntityType::class, [
                'class' => Equipes::class,
                'disabled' => $disabled,
            ])
            ->add('estLecteur', ChoiceType::class, [
                'choices' => ['null' => null,
                    'E' => 0,
                    'L' => 1,
                    'R' => 2],

                'label' => 'Le juré est lecteur(L), examinateur(E) ou rapporteur(R) ou n\'examine pas(null)',

            ]);


    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([

            'data_class' => Attributions::class,
            'page' => null

        ]);
    }


}




