<?php

namespace App\Controller\Form\Filter;

use Doctrine\ORM\QueryBuilder;

use EasyCorp\Bundle\EasyAdminBundle\Form\Type\FiltersFormType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;


class AutorisationsFilterType extends FiltersFormType
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;

    }

    public function filter(QueryBuilder $queryBuilder, FormInterface $form, array $metadata): QueryBuilder
    {

        $qb = $queryBuilder;
        $datas = $form->getParent()->getData();


        $this->requestStack->getSession()->set('edition_titre', $this->requestStack->getSession()->get('edition')->getEd());


        if (isset($datas['eleve'])) {
            if (null !== $datas['eleve']) {
                $queryBuilder->andWhere('entity.eleve =:eleve')
                    ->setParameter('eleve', $datas['eleve']);

            }
        }
        if (isset($datas['equipe'])) {
            if (null !== $datas['equipe']) {

                $queryBuilder->setParameter('edition', $datas['equipe']->getEdition())
                    ->andWhere('entity.equipe =:equipe')
                    ->setParameter('equipe', $datas['equipe']);

            }
        }

        if (isset($datas['prof'])) {

            if (null !== $datas['prof']) {

                $queryBuilder->andWhere('entity.prof =:prof')
                    ->setParameter('prof', $datas['prof']);

            }
        }


        return $queryBuilder;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choice_label' => [
                'Eleve' => 'eleve',
                'Professeur' => 'prof',
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



