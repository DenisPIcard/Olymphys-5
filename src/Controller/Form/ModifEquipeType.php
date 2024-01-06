<?php

namespace App\Controller\Form;

use App\Entity\Equipesadmin;
use datetime;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ModifEquipeType extends AbstractType
{
    private SessionInterface $session;

    public function __construct(RequestStack $requestStack)
    {
        $this->session = $requestStack->getSession();

    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $rne = $options['rne'];
        $eleves = $options['eleves'];
        $nbEleves = count($eleves);

        $required = [true, true, false, false, false, false];

        $datelim = $this->session->get('datelimphotoscia');
        new datetime('now') > $datelim ? $tag = true : $tag = false;
        $builder->add('titreProjet', TextType::class, [
            'label' => 'Titre du projet',
            'mapped' => true,
            'disabled' => $tag,
        ])
            ->add('idProf1', EntityType::class, [
                'class' => User::class,
                'query_builder' => function (EntityRepository $er) use ($rne) {
                    return $er->createQueryBuilder('u')
                        ->andWhere('u.rne =:rne')
                        ->setParameter('rne', $rne)
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
                'query_builder' => function (EntityRepository $er) use ($rne) {
                    return $er->createQueryBuilder('u')
                        ->andWhere('u.rne =:rne')
                        ->setParameter('rne', $rne)
                        ->addOrderBy('u.nom', 'ASC');
                },
                'choice_value' => 'getId',
                'choice_label' => 'getPrenomNom',

                'mapped' => true,

            ]);
        $i = 1;
        foreach ($eleves as $eleve) {

            $builder->add('prenomeleve' . $i, TextType::class, [
                'mapped' => false,
                'empty_data' => $eleves[$i - 1]->getPrenom(),
                'data' => $eleves[$i - 1]->getPrenom(),
                'required' => $required[$i - 1],
            ])
                ->add('nomeleve' . $i, TextType::class, [
                    'mapped' => false,
                    'empty_data' => $eleves[$i - 1]->getNom(),
                    'data' => $eleves[$i - 1]->getNom(),
                    'required' => $required[$i - 1],
                ])
                ->add('maileleve' . $i, EmailType::class, [
                    'mapped' => false,
                    'empty_data' => $eleves[$i - 1]->getCourriel(),
                    'data' => $eleves[$i - 1]->getCourriel(),
                    'required' => $required[$i - 1],
                ])
                ->add('classeeleve' . $i, ChoiceType::class, [
                    'choices' => [' ' => null,
                        '2nde' => '2nde',
                        '1ère' => '1ere',
                        'Term' => 'Term',
                    ],
                    'mapped' => false,
                    'data' => $eleves[$i - 1]->getClasse(),
                    'placeholder' => $eleves[$i - 1]->getClasse(),
                    'required' => $required[$i - 1],

                ])
                ->add('genreeleve' . $i, ChoiceType::class, [
                    'mapped' => false,
                    'data' => $eleves[$i - 1]->getGenre(),
                    'placeholder' => $eleves[$i - 1]->getGenre(),
                    'required' => $required[$i - 1],
                    'choices' => [' ' => null,
                        'F' => 'F',
                        'M' => 'M']])
                ->add('id' . $i, HiddenType::class, [
                    'mapped' => false,
                    'data' => $eleves[$i - 1]->getId()
                ]);

            $i++;
        }

        for ($i = $nbEleves + 1; $i < 7; $i++) {


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
                        '2de' => '2de',
                        '1ère' => '1ère',
                        'Term' => 'Term',
                    ],
                    'mapped' => false,
                    'required' => $required[$i - 1],
                ])
                ->add('genreeleve' . $i, ChoiceType::class, [
                    'mapped' => false,
                    'required' => $required[$i - 1],
                    'choices' => [' ' => null,
                        'F' => 'F',
                        'M' => 'M']])
                ->add('id' . $i, HiddenType::class, [
                    'mapped' => false,
                    'data' => '0'
                ]);
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
            /* ->add('recompense',TextType::class,[
                     'mapped' => true,
                     'required'=>false,
                    ])*/
            ->add('origineprojet', TextType::class, [
                'mapped' => true,
                'required' => true,
            ])
            ->add('description', TextareaType::class, [

                'required' => true,
                'mapped' => true,

            ])
            ->add('save', SubmitType::class)
            ->add('inscrite', CheckboxType::class, [
                'value' => 1,
                'required' => false,
                'mapped' => true,

            ]);


    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Equipesadmin::class, 'rne' => null, 'eleves' => null]);

    }
}

