<?php

namespace App\Controller\Form\Filter;

use Doctrine\ORM\QueryBuilder;
use  EasyCorp\Bundle\EasyAdminBundle\Form\Type\FiltersFormType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ProfesseursFilterType extends FiltersFormType
{


    public function filter(QueryBuilder $queryBuilder, FormInterface $form, array $metadata): void
    {

        $datas = $form->getParent()->getData();


        if (isset($datas['edition'])) {
            
            $queryBuilder
                ->select(Edition::class)
                ->where('entite.edition =:edition')
                ->setParameter('edition', $datas['edition']);

        }


    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choice_label' => [
                'Edition' => 'edition',

                // ...
            ],
        ]);

    }

    public function getParent(): string
    {
        return EntityType::class;
    }


}



