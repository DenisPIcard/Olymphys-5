<?php
//App/Form/ProfileType.php

namespace App\Controller\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileType extends AbstractType
{
    protected string $translationDomain = 'App/translations'; //

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, ['required' => true, 'label' => 'Votre nom'])
            ->add('prenom', TextType::class, ['required' => true, 'label' => 'Votre prénom'])
            ->add('adresse', TextType::class, ['required' => true, 'label' => 'Votre adresse (numéro +rue)'])
            ->add('ville', TextType::class, ['required' => true, 'label' => 'Votre ville'])
            ->add('code', TextType::class, ['required' => true, 'label' => 'Votre code'])
            ->add('phone', TextType::class, ['required' => true, 'label' => 'Votre téléphone, portable, si possible',])
            ->add('rne', TextType::class, ['required' => false, 'label' => 'RNE, si vous comptez inscrire une équipe'])
            ->add('Modification', SubmitType::class, ['label' => 'Valider ces modifications']);;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            // enable/disable CSRF protection for this form
            'csrf_protection' => true,
            // the name of the hidden HTML field that stores the token
            'csrf_field_name' => '_token',
            // an arbitrary string used to generate the value of the token
            // using a different string for each form improves its security
            'csrf_token_id' => 'task_item',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'user_registration';
    }
}
