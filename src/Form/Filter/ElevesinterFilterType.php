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


class ElevesinterFilterType extends FilterType
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

            $queryBuilder->Where('equipe.edition =:edition')
                ->setParameter('edition', $datas['edition']);
            $this->session->set('edition_titre', $datas['edition']->getEd());
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

    public function getParent()
    {
        return EntityType::class;
    }


}



