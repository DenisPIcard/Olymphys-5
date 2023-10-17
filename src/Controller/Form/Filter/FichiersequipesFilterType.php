<?php

namespace App\Controller\Form\Filter;

use Doctrine\ORM\QueryBuilder;
use  EasyCorp\Bundle\EasyAdminBundle\Form\Type\FiltersFormType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;


class FichiersequipesFilterType extends FiltersFormType
{
    private RequestStack $requestStack;
    
    
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
        
    }
    
    public function filter(QueryBuilder $queryBuilder, FormInterface $form, array $metadata): QueryBuilder
    {
        
        $datas = $form->getParent()->getData();
        
        if (null !== $datas['edition']) {
            
            $queryBuilder->andWhere('entity.edition =:edition')
                ->setParameter('edition', $datas['edition']);
            $this->requestStack->getSession()->set('edition_titre', $datas['edition']->getEd());
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



