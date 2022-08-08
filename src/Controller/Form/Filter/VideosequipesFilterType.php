<?php

namespace App\Controller\Form\Filter;

use Doctrine\ORM\QueryBuilder;
use  EasyCorp\Bundle\EasyAdminBundle\Form\TYpe\FiltersFormType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;


class VideosequipesFilterType extends FiltersFormType
{


    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;

    }

    public function filter(QueryBuilder $queryBuilder, FormInterface $form, array $metadata): QueryBuilder
    {

        $datas = $form->getParent()->getData();
        if (!isset($datas['edition'])) {

            $this->requestStack->getSession()->set('edition_titre', $this->requestStack->getSession()->get('edition')->getEd());
        }

        if (isset($datas['edition'])) {

            $queryBuilder->andWhere('entity.edition =:edition')
                ->setParameter('edition', $datas['edition']);
            $this->requestStack->getSession()->set('edition_titre', $datas['edition']->getEd());
        }
        if (isset($datas['centre'])) {

            $queryBuilder->andWhere('eq.centre =:centre')
                ->setParameter('centre', $datas['centre'])
                ->orderBy('eq.numero', 'ASC');

        }
        if (isset($datas['equipe'])) {

            $queryBuilder->andWhere('eq.id =:id')
                ->setParameter('id', $datas['equipe'])
                ->orderBy('eq.numero', 'ASC');

        }

        return $queryBuilder;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choice_label' => [
                'Edition' => 'edition',
                'Centre' => 'centre',
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



