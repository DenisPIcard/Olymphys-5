<?php

namespace App\Form;

use App\Entity\Odpf\OdpfImagescarousels;

use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use FM\ElfinderBundle\Form\Type\ElFinderType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichFileType;

class OdpfChargeDiapoType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('image', ElfinderType::class, [

                'required' => false,
                'mapped' => false,
                'label' => 'Cliquer pour choisir le fichier sur le site Olymphys',
                'attr' => ['class' => 'form-control', 'readonly' => 'readonly'],
                'instance' => 'form',


            ])
            ->add('imageFile', FileType::class, [

                'required' => false,

                //'mapped'=>false,
                'label' => 'Cliquer pour choisir le fichier sur votre ordinateur',


            ])
            ->add('coment', TextType::class, [
                'label' => 'Commentaire',
                'required' => false
            ])
            ->add('save', SubmitType::class);


    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([

            'data_class' => OdpfImagescarousels::class,

        ]);
    }


}




