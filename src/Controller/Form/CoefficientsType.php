<?php

namespace App\Controller\Form;

use App\Entity\Coefficients;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CoefficientsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('qualexp')
            ->add('demscien')
            ->add('preoral')
            ->add('origina')
            ->add('traequi')
            ->add('memoire');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Coefficients::class,
        ]);
    }
}
