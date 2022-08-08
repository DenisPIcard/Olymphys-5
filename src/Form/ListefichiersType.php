<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ListefichiersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {


        $builder
            // ...
            ->add('fichier', HiddenType::class, ['disabled' => true, 'label' => false, 'mapped' => false])
            ->add('equipe', HiddenType::class, ['disabled' => true, 'label' => false, 'mapped' => false])
            ->add('id', HiddenType::class, ['disabled' => true, 'label' => false, 'mapped' => false])
            ->add('save', SubmitType::class);


        //->add('lettre',EntityType::class,[               'class' =>User::class,               'choice_label'=>'getlettre',     'multiple' => false ]) // ...

    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => null,
        ]);
    }
}