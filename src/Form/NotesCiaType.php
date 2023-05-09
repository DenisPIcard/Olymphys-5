<?php

namespace App\Form;

use App\Entity\Cia\NotesCia;
use App\Entity\Notes;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NotesCiaType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->EST_PasEncoreNotee = $options['EST_PasEncoreNotee'];
        $this->EST_Lecteur = $options['EST_Lecteur'];

        // $EST_PasEncoreNotee, $EST_Lecteur
        $choix['Excellent'] = Notes::EXCELLENT;
        $choix['Bien'] = Notes::BIEN;
        $choix['Moyen'] = Notes::MOYEN;
        $choix['Insuffisant'] = Notes::INSUFFISANT;

        if ($options['EST_PasEncoreNotee']) {
            if ($options['EST_Lecteur']) {

                $builder
                    ->add('exper', ChoiceType::class, array('choices' => $choix, 'placeholder' => 'Évaluer',))
                    ->add('demarche', ChoiceType::class, array('choices' => $choix, 'placeholder' => 'Évaluer',))
                    ->add('oral', ChoiceType::class, array('choices' => $choix, 'placeholder' => 'Évaluer',))
                    ->add('repquestions', ChoiceType::class, array('choices' => $choix, 'placeholder' => 'Évaluer',))
                    ->add('origin', ChoiceType::class, array('choices' => $choix, 'placeholder' => 'Évaluer',))
                    ->add('Wgroupe', ChoiceType::class, array('choices' => $choix, 'placeholder' => 'Évaluer',))
                    ->add('ecrit', ChoiceType::class, array('choices' => $choix, 'placeholder' => 'Évaluer',))
                    ->add('Enregistrer', SubmitType::class);

            } else {

                $builder
                    ->add('exper', ChoiceType::class, array('choices' => $choix, 'placeholder' => 'Évaluer',))
                    ->add('demarche', ChoiceType::class, array('choices' => $choix, 'placeholder' => 'Évaluer',))
                    ->add('oral', ChoiceType::class, array('choices' => $choix, 'placeholder' => 'Évaluer',))
                    ->add('repquestions', ChoiceType::class, array('choices' => $choix, 'placeholder' => 'Évaluer',))
                    ->add('origin', ChoiceType::class, array('choices' => $choix, 'placeholder' => 'Évaluer',))
                    ->add('Wgroupe', ChoiceType::class, array('choices' => $choix, 'placeholder' => 'Évaluer',))
                    ->add('Enregistrer', SubmitType::class);

            }
        } else {
            if ($options['EST_Lecteur']) {
                $builder
                    ->add('exper', ChoiceType::class, array('choices' => $choix,))
                    ->add('demarche', ChoiceType::class, array('choices' => $choix,))
                    ->add('oral', ChoiceType::class, array('choices' => $choix,))
                    ->add('repquestions', ChoiceType::class, array('choices' => $choix,))
                    ->add('origin', ChoiceType::class, array('choices' => $choix,))
                    ->add('Wgroupe', ChoiceType::class, array('choices' => $choix,))
                    ->add('ecrit', ChoiceType::class, array('choices' => $choix,))
                    ->add('Enregistrer', SubmitType::class);

            } else {
                $builder
                    ->add('exper', ChoiceType::class, array('choices' => $choix,))
                    ->add('demarche', ChoiceType::class, array('choices' => $choix,))
                    ->add('oral', ChoiceType::class, array('choices' => $choix,))
                    ->add('repquestions', ChoiceType::class, array('choices' => $choix,))
                    ->add('origin', ChoiceType::class, array('choices' => $choix,))
                    ->add('Wgroupe', ChoiceType::class, array('choices' => $choix,))
                    ->add('Enregistrer', SubmitType::class);

            }
        }

    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => NotesCia::class,
            'EST_PasEncoreNotee' => true,
            'EST_Lecteur' => true,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'cyberjuryCia_notes';
    }


}