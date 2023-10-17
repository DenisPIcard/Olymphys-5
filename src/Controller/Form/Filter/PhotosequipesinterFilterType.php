<?php

namespace App\Controller\Form\Filter;

use Doctrine\ORM\QueryBuilder;
use  EasyCorp\Bundle\EasyAdminBundle\Form\Type\FiltersFormType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;


class PhotosequipesinterFilterType extends FiltersFormType
{
    private RequestStack $requestStack;
    
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
        
    }
    
    public function filter(QueryBuilder $queryBuilder, FormInterface $form, array $metadata): QueryBuilder
    {
        
        
        $datas = $form->getParent()->getData();
        //dd( $datas);
        if (null !== $datas['edition']) {
            
            $queryBuilder->andWhere('entity.edition =:edition')
                ->andWhere('entity.national =:national')
                ->setParameter('national', 'FALSE')
                ->setParameter('edition', $datas['edition']);
            $this->requestStack->getSession()->set('edition_titre', $datas['edition']->getEd());
        }
        if (null !== $datas['centre']) {
            
            $queryBuilder->andWhere('eq.centre=:centre')
                ->setParameter('centre', $datas['centre'])
                ->andWhere('entity.national =:national')
                ->setParameter('national', 'FALSE');
            
        }
        
        
        if (null != $datas['equipe']) {
            
            $queryBuilder->andWhere('entity.equipe =:equipe')
                ->setParameter('edition', $datas['equipe']->getEdition())
                ->setParameter('equipe', $datas['equipe']);
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



