<?php

namespace App\Form\Filter;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\FilterType;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\FilterTypeTrait;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

//use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


class PhotosinterFilterType extends FilterType
{

    public function filter(QueryBuilder $queryBuilder, FormInterface $form, array $metadata)
    {


        $datas = $form->getParent()->getData();

        if (isset($datas['edition'])) {

            $queryBuilder->andWhere('entity.edition =:edition')
                ->setParameter('edition', $datas['edition']);
        }
        if (isset($datas['centre'])) {

            $queryBuilder->andWhere('eq.centre=:centre')
                ->setParameter('centre', $datas['centre']);


        }

        //dd($datas['equipe']);
        if (isset($datas['equipe'])) {

            $queryBuilder->andWhere('entity.equipe =:equipe')
                ->setParameter('equipe', $datas['equipe'])
                ->addOrderBy('eq.numero', 'ASC');

        }


        return $queryBuilder;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choice_label' => [
                'Edition' => 'edition',
                'Equipe' => 'equipe'
                // ...
            ],
        ]);

    }

    public function getParent(): string
    {
        return EntityType::class;
    }


}



