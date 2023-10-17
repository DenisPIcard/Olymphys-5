<?php

namespace App\Controller\Admin\Field;


use App\Entity\Equipesadmin;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfType extends AbstractType
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }


    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $equipe = $options['equipe'];
        $qb = $this->em->getRepository(Equipesadmin::class)->createQueryBuilder('e');


        $builder
            ->add('cadeau', ChoiceType::class, [
                'choices' => $qb->leftJoin('entity.prof1', 'p')
                    ->where('p.rneId = rne')
                    ->setParameter('rne', $equipe->getUaiId())


            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\Equipesadmin',
            'Equipe' => null,
        ));
    }

}