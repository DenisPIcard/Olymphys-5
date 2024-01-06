<?php

namespace App\Controller\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EquipesType extends AbstractType
{
    /**
     * @var bool
     */
    private bool $Modifier_Rang;
    /**
     * @var bool
     */
    private bool $Attrib_Phrases;
    /**
     * @var bool
     */
    private bool $Attrib_Cadeaux;
    /**
     * @var bool
     */
    private bool $Deja_Attrib;
    /**
     * @var bool
     */
    private $Attrib_Couleur;

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->Modifier_Rang = $options['Modifier_Rang'];
        $this->Attrib_Phrases = $options['Attrib_Phrases'];
        $this->Attrib_Cadeaux = $options['Attrib_Cadeaux'];
        $this->Deja_Attrib = $options['Deja_Attrib'];
        $this->Attrib_Couleur = $options['Attrib_Couleur'];


        if ($options['Modifier_Rang']) {
            $builder
                ->add('rang', IntegerType::class) // au lieu de TextType
                ->add('Enregistrer', SubmitType::class);
        } elseif ($options['Attrib_Phrases']) {
            $builder
                ->add('phrases', PhrasesType::class)
                ->add('liaison', EntityType::class, [
                    'class' => Liaison::class,
                    'choice_label' => 'getLiaison',
                    'multiple' => false])
                ->add('Enregistrer', SubmitType::class);
        } elseif ($options['Attrib_Couleur']) {
            $builder
                ->add('couleur', ChoiceType::class, [
                    'choices' => ['0' => null,
                        '1er' => 'danger',
                        '2ème' => 'warning',
                        '3ème' => 'primary',]
                ])
                ->add('Enregistrer', SubmitType::class);


        } elseif ($options['Attrib_Cadeaux']) {
            if ($options['Deja_Attrib']) {
                $builder
                    ->add('cadeau', CadeauxType::class)
                    ->add('Enregistrer', SubmitType::class);
            } else {
                $builder
                    ->add('cadeau', EntityType::class, [
                        'class' => Cadeaux::class,
                        'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('c')
                                ->where('c.attribue = 0')
                                ->orderBy('c.montant', 'DESC');
                        },
                        'choice_label' => 'displayCadeau',
                        'multiple' => false])
                    ->add('Enregistrer', SubmitType::class);

            }
        }

    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\Equipes',
            'Modifier_Rang' => false,
            'Attrib_Phrases' => false,
            'Attrib_Cadeaux' => false,
            'Deja_Attrib' => false,
            'Attrib_Couleur' => false,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'cyberjury_equipes';
    }


}