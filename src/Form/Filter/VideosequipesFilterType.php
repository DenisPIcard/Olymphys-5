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


class VideosequipesFilterType extends FilterType
{
    use FilterTypeTrait;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;

    }

    public function filter(QueryBuilder $queryBuilder, FormInterface $form, array $metadata)
    {

        $datas = $form->getParent()->getData();
        if (!isset($datas['edition'])) {

            $this->session->set('edition_titre', $this->session->get('edition')->getEd());
        }

        if (isset($datas['edition'])) {

            $queryBuilder->andWhere('entity.edition =:edition')
                ->setParameter('edition', $datas['edition']);
            $this->session->set('edition_titre', $datas['edition']->getEd());
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



