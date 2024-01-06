<?php

namespace App\Controller\Form\Filter;

use Doctrine\ORM\QueryBuilder;
use  EasyCorp\Bundle\EasyAdminBundle\Form\Type\FiltersFormType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EquipesFilterType extends FiltersFormType
{

    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;

    }

    public function filter(QueryBuilder $queryBuilder, FormInterface $form, array $datas): void
    {
        
        $datas = $form->getParent()->getData();
        //dd($datas);
        $listparam = array();
        if (!isset($datas['edition'])) {

            $this->requestStack->getSession()->set('edition_titre', $this->requestStack->getSession()->get('edition')->getEd());
        }
        if (isset($datas['edition'])) {
            $listparam['edition_'] = $datas['edition'];
            $this->requestStack->getSession()->set('edition_titre', $datas['edition']->getEd());
        }

        //$queryBuilder->expr()->eq();


        if (isset($datas['edition'])) {

            $queryBuilder->leftJoin('entity.infoequipe', 'u')
                ->andWhere('u.edition =:edition')
                ->setParameter('edition', $datas['edition']);

        }


    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choice_label' => [
                'Edition' => 'infoequipe',

                // ...
            ],
        ]);

    }

    public function getParent(): string
    {
        return EntityType::class;
    }


}



