<?php

namespace App\Controller\Form\Filter;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\FiltersFormType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ElevesinterFilterType extends FiltersFormType
{

    private RequestStack $requestStack;


    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function filter(QueryBuilder $queryBuilder, FormInterface $form, array $metadata)
    {

        $datas = $form->getParent()->getData();
        if (!isset($datas['edition'])) {

            $this->requestStack->getSession()->set('edition_titre', $this->requestStack->getSession()->get('edition')->getEd());
        }
        if (isset($datas['edition'])) {

            $queryBuilder->Where('equipe.edition =:edition')
                ->setParameter('edition', $datas['edition']);
            $this->requestStack->getSession()->set('edition_titre', $datas['edition']->getEd());
        }
        if (isset($datas['equipe'])) {

            $queryBuilder->andWhere('entity.equipe =:equipe')
                ->setParameter('equipe', $datas['equipe'])
                ->orderBy('equipe.numero', 'ASC');

        }


    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choice_label' => [
                'Edition' => 'edition',
                'Equipe' => 'equipe',
                // ...
            ],
        ]);

    }

    public function getParent(): string
    {
        return EntityType::class;
    }


}



