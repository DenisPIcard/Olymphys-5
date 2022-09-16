<?php


namespace App\Form\Type\Admin;

use App\Entity\Equipesadmin;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;

use Symfony\Component\OptionsResolver\OptionsResolver;

//use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


class CustomEquipespasseesFilterType extends AbstractType
{


    public function configureOptions(OptionsResolver $resolver)

    {
        $edition = $_SESSION['_sf2_attributes']['edition'];
        $resolver->setDefaults([
            'comparison_type_options' => ['type' => 'entity'],
            'value_type' => EntityType::class,
            'class' => Equipesadmin::class,
            'query_builder' => function (EntityRepository $er) use ($edition) {
                return $er->createQueryBuilder('u')
                    ->andWhere('u.edition =:edition')
                    ->setParameter('edition', $edition)
                    ->addOrderBy('u.numero', 'ASC');
            },
        ]);

    }

    public function getParent(): string
    {
        return EntityType::class;
    }


}
