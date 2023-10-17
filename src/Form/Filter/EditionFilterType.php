<?php

namespace App\Form\Filter;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\FilterType;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\FilterTypeTrait;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

use App\Entity\Edition;
use App\Entity\Equipesadmin;

class EditionFilterType extends FilterType
{
    use FilterTypeTrait;

    public function filter(QueryBuilder $queryBuilder, FormInterface $form, array $metadata)
    {
        $alias = \current($queryBuilder->getRootAliases());
        $property = $metadata['property'];
        $paramName = static::createAlias($property);
        $edition = $form->getData();
        // use $metadata['property'] to make this query generic
        $queryBuilder->andWhere('entity.ed =: edition')
            ->setParameter('edition', $edition);


        // ...
    }


    public function configureOptions(OptionsResolver $resolver)
    {


        $resolver->setDefaults([
            'class' => Edition::class,
            'choice_label' => 'ed',


        ]);

    }

    public function getParent(): string
    {
        return EntityType::class;
    }


}



