<?php

namespace App\Controller\Form;

use App\Entity\Totalequipes;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ToutfichiersType extends AbstractType
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {


        switch ($options['data']['choix']) {
            case '0':
                $choice = ['Mémoire(pdf, 2,5 M max, 20 pages)' => 0];

                break;
            case '1':
                $choice = ['Annexe(pdf, 2,5 M max, 20 pages)' => 1];

                break;
            case '2':
                $choice = ['Résumé(pdf, 1 M max, 1 page)' => 2];

                break;

            case '3':
                $choice = ['Diaporama  pour le jury(pdf, 10 M maxi)et qui sera publiée après le concours' => 3];

                break;
            case '4':
                $choice = ['Fiche sécurité(1M max, doc, docx, pdf, jpg, odt)' => 4];

                break;
            case '5':
                $choice = ['Diaporama  pour le jury(pdf, 10 M maxi)' => 5];

                break;
            case '6':
                $choice = ['Autorisations photos (pdf,1M max )' => 6,];

                break;
            case '7':
                $choice = ['Questionnaire équipe(1M max, doc, docx, pdf, jpg, odt)' => 7];

                break;
        }


        $builder
            // ...
            ->add('fichier', FileType::class, [
                'label' => 'Choisir le fichier ',
                'mapped' => false,

                'required' => false,

            ]);

        $builder->add('typefichier', ChoiceType::class, [
            'mapped' => false,
            'required' => false,
            'multiple' => false,
            'placeholder' => array_key_first($choice),
            'empty_data' => strval($choice[array_key_first($choice)]),
            'choices' => $choice,
            'disabled' => true
        ])
            ->add('choice', HiddenType::class, [
                'data' => $choice[array_key_first($choice)],
            ]);

        $builder->add('save', SubmitType::class);


    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => null, 'choix' => null]);
    }
}