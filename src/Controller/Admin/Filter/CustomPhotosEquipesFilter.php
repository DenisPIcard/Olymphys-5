<?php

namespace App\Controller\Admin\Filter;

use App\Entity\Odpf\OdpfEquipesPassees;

use App\Form\Type\Admin\CustomEquipespasseesFilterType;
use App\Form\Type\Admin\CustomPhotosEquipesFilterType;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;


class CustomPhotosEquipesFilter implements FilterInterface
{
    use FilterTrait;

    public static function new(string $propertyName, $label = null): self
    {
        return (new self())
            ->setFilterFqcn(__CLASS__)
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType(CustomPhotosEquipesFilterType::class);
    }

    public function apply(QueryBuilder $queryBuilder, FilterDataDto $filterDataDto, ?FieldDto $fieldDto, EntityDto $entityDto): void
    {

        $queryBuilder
            ->andWhere('entity.equipe =:value')
            ->setParameter('value', $filterDataDto->getValue());


    }


}