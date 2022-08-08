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


class FichiersequipesFilterType extends FilterType
{
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;

    }

    public function filter(QueryBuilder $queryBuilder, FormInterface $form, array $metadata)
    {

        $datas = $form->getParent()->getData();

        if (null !== $datas['edition']) {

            $queryBuilder->andWhere('entity.edition =:edition')
                ->setParameter('edition', $datas['edition']);
            $this->session->set('edition_titre', $datas['edition']->getEd());
        }
        if (isset($datas['centre'])) {
            if (null !== $datas['centre']) {
                $queryBuilder->andWhere('eq.centre=:centre')
                    ->setParameter('centre', $datas['centre']);
            }
        }
        if (isset($datas['equipe'])) {
            if (null !== $datas['equipe']) {

                $queryBuilder->setParameter('edition', $datas['equipe']->getEdition())
                    ->andWhere('entity.equipe =:equipe')
                    ->setParameter('equipe', $datas['equipe']);

            }
        }
        return $queryBuilder;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choice_label' => [
                'Edition' => 'edition',
                'Centre' => 'centre',
                // ...
            ],
        ]);

    }

    public function getParent()
    {
        return EntityType::class;
    }


}



