<?php

namespace App\Form;

use App\Entity\Equipesadmin;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InscrireEquipeType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $uai = $options['uai'];
        $required = [true, true, false, false, false, false];
        $builder->add('titreProjet', TextType::class, [
            'label' => 'Titre du projet',
            'mapped' => true
        ])
            ->add('idProf1', EntityType::class, [
                'class' => User::class,
                'query_builder' => function (EntityRepository $er) use ($uai) {
                    return $er->createQueryBuilder('u')
                        ->andWhere('u.uai =:uai')
                        ->andWhere('u.isActive = 1')
                        ->setParameter('uai', $uai)
                        ->addOrderBy('u.nom', 'ASC');
                },
                'choice_value' => 'getId',
                'choice_label' => 'getPrenomNom',
                'mapped' => true,
                'required' => true,
            ])
            ->add('idProf2', EntityType::class, [
                'class' => User::class,
                'required' => false,
                'query_builder' => function (EntityRepository $er) use ($uai) {
                    return $er->createQueryBuilder('u')
                        ->andWhere('u.uai =:uai')
                        ->setParameter('uai', $uai)
                        ->andWhere('u.isActive = 1')
                        ->addOrderBy('u.nom', 'ASC');
                },
                'choice_value' => 'getId',
                'choice_label' => 'getPrenomNom',

                'mapped' => true,

            ]);
        for ($i = 1; $i < 7; $i++) {

            $builder->add('prenomeleve' . $i, TextType::class, [
                'mapped' => false,
                'required' => $required[$i - 1],
            ])
                ->add('nomeleve' . $i, TextType::class, [
                    'mapped' => false,
                    'required' => $required[$i - 1],
                ])
                ->add('maileleve' . $i, EmailType::class, [
                    'mapped' => false,
                    'required' => $required[$i - 1],
                ])
                ->add('classeeleve' . $i, ChoiceType::class, [
                    'choices' => [' ' => null,
                        '2nde' => '2nde',
                        '1ère' => '1ere',
                        'Term' => 'Term',
                    ],
                    'mapped' => false,
                    'required' => $required[$i - 1],
                    'empty_data' => null,
                    'placeholder' => null,
                ])
                ->add('genreeleve' . $i, ChoiceType::class, [
                    'mapped' => false,
                    'required' => $required[$i - 1],
                    'empty_data' => null,
                    'placeholder' => null,
                    'choices' => [' ' => null,
                        'F' => 'F',
                        'M' => 'M']]);
        }


        $builder->add('partenaire', TextType::class, [
            'mapped' => true,
            'required' => false,
        ])
            ->add('contribfinance', ChoiceType::class, [
                'mapped' => true,
                'required' => true,
                'empty_data' => ' ',
                'choices' => ['Prof1-avec versement anticipé de la contribution financière' => 'Prof1-avec versement anticipé de la contribution',
                    'Prof1-avec remboursement à postériori des frais engagés' => 'Prof1-avec remboursement à postériori des frais engagés',
                    'Prof2-avec versement anticipé de la contribution financière' => 'Prof2-avec versement anticipé de la contribution',
                    'Prof2-avec remboursement à postériori des frais engagés' => 'Prof2-avec reboursement à postériori des frais engagés',
                    'Gestionnaire du lycée' => 'Gestionnaire du lycée',
                    'Autre' => 'Autre'
                ],

            ])
            ->add('origineprojet', TextType::class, [
                'mapped' => true,
                'required' => true,
            ])
            ->add('description', TextType::class, [

                'required' => true,
                'mapped' => true,

            ])
            ->add('save', SubmitType::class)
            ->add('inscrite', CheckboxType::class, [
                'value' => 1,
                'required' => true,
                'mapped' => true,

            ]);


    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => Equipesadmin::class, 'uai' => null]);

    }
}