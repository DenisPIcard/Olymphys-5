<?php

namespace App\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use App\Entity\Fichessecur;
use App\Entity\Fichiersequipes;
use App\Entity\Totalequipes;
use App\Entity\Presentation;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\TypeEntityType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Validator\Constraints\File;

class PresentationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            // ..
            ->add('fichier', FileType::class, [
                'label' => 'Choisir le fichier de la présentation( .pdf, .xpdf acceptés uniquement  avec moins de 1 M, une seule page )',
                'mapped' => false,

                // make it optional so you don't have to re-upload the PDF file
                // everytime you edit the Product details
                'required' => false,

                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '100000k',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf'

                        ],
                        'mimeTypesMessage' => 'Attention, votre fichier ne correspond pas au format imposé !',
                    ])
                ],

            ])
            ->add('save', SubmitType::class);
        //->add('lettre',EntityType::class,[               'class' =>'App:Equipes',               'choice_label'=>'getlettre',     'multiple' => false ]) // ...

    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Fichiersequipes::class,
        ]);
    }
}