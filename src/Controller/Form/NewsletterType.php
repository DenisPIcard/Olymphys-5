<?php

namespace App\Controller\Form;

use App\Entity\Newsletter;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\Configurator\FOSCKEditorTypeConfigurator;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class NewsletterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $texteini = $options['textini'];
        $builder->add('name', TextType::class)
            ->add('texte', CKEditorType::class, [
                'label' => 'Saisir le texte de la newsletter',
                'data' => $texteini,
                'input_sync' => true
            ])
            ->add('destinataires', ChoiceType::class, [

                'choices' => ['Tous' => 'Tous', 'Professeurs' => 'Professeurs'], //'Elèves'=>'Eleves'
                'empty_data' => 'Tous'
            ])
            ->add('save', SubmitType::class, ['label' => 'Valider']);

    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'data_class' => Newsletter::class,
            'textini' => null,
        ));
    }


}