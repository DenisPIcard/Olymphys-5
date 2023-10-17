<?php

namespace App\Controller\Form\Filter;

use Doctrine\ORM\QueryBuilder;
use  EasyCorp\Bundle\EasyAdminBundle\Form\Type\FiltersFormType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;


class PhotosequipescnFilterType extends FiltersFormType
{
    private RequestStack $requestStack;
    
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
        
    }
    
    public function filter(QueryBuilder $queryBuilder, FormInterface $form, array $metadata): QueryBuilder
    {
        
        
        $datas = $form->getParent()->getData();
        
        if (null != $datas['edition']) {
            
            $queryBuilder->andWhere('entity.edition =:edition')
                ->setParameter('edition', $datas['edition']);
            $this->requestStack->getSession()->set('edition_titre', $datas['edition']->getEd());
        }
        
        
        if (null != $datas['equipe']) {
            
            
            $queryBuilder->setParameter('edition', $datas['equipe']->getEdition())
                ->andWhere('entity.equipe =:equipe')
                ->setParameter('equipe', $datas['equipe'])
                ->addOrderBy('eq.lettre', 'ASC');
            $this->requestStack->getSession()->set('edition_titre', $datas['equipe']->getEdition()->getEd());
        }
        
        
        return $queryBuilder;
        
    }
    
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choice_label' => [
                'Edition' => 'edition',
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



