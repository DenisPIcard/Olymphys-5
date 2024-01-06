<?php

namespace App\Controller\Form\Filter;

use Doctrine\ORM\QueryBuilder;
use  EasyCorp\Bundle\EasyAdminBundle\Form\Type\FiltersFormType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;


class EquipesadminFilterType extends FiltersFormType
{

    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;

    }

    public function filter(QueryBuilder $queryBuilder, FormInterface $form, array $metadata): void
    {

        $datas = $form->getParent()->getData();
        $listparam = array();
        if (!isset($datas['edition'])) {

            $this->requestStack->getSession()->set('edition_titre', $this->requestStack->getSession()->get('edition')->getEd());
        }
        if (isset($datas['edition'])) {
            $listparam['edition_'] = $datas['edition'];
            $this->requestStack->getSession()->set('edition_titre', $datas['edition']->getEd());
        }
        if (isset($datas['centre'])) {
            $centres = $datas['centre'];
            $n = 0;
            foreach ($centres as $centre) {
                $listparam['centre' . $n] = $centre;
                $n++;
            }
            unset($centre);;
        }
        //$queryBuilder->expr()->eq();


        if (isset($datas['edition'])) {

            $queryBuilder->Where('entity.edition =:edition')
                ->setParameter('edition', $datas['edition']);

        }
        if (isset($datas['centre'])) {

            $queryBuilder->andWhere('entity.centre =:centre')
                ->setParameter('centre', $datas['centre'])
                ->addOrderBy('entity.numero', 'ASC');

        }


    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choice_label' => [
                'Edition' => 'edition',
                'Centre' => 'centre',
                // ...
            ],
        ]);

    }

    public function getParent(): string
    {
        return EntityType::class;
    }


}



