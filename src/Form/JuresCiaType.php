<?php

namespace App\Form;

use App\Entity\Cia\JuresCia;
use App\Entity\Equipesadmin;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class JuresCiaType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomJure', TextType::class)
            ->add('prenomJure', TextType::class)
            ->add('initialesJure', TextType::class)
            ->add('equipes', CollectionType::class, ['allow_add' => true])
            ->add('valider', SubmitType::class);

    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'data_class' => JuresCia::class,

        ));
    }


}