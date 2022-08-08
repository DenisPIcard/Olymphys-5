<?php

namespace App\Form\Filter;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\FilterType;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\FilterTypeTrait;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

//use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


class AutorisationsFilterType extends FilterType
{
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;

    }

    public function filter(QueryBuilder $queryBuilder, FormInterface $form, array $metadata)
    {

        $qb = $queryBuilder;
        $datas = $form->getParent()->getData();


        $this->session->set('edition_titre', $this->session->get('edition')->getEd());


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

    public function getParent()
    {
        return EntityType::class;
    }


}



