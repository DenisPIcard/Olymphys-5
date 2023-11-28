<?php

namespace App\Controller\Admin\Filter;

use App\Form\Type\Admin\CustomEquipeFichierFilterType;
use App\Form\Type\Admin\CustomEquipeFilterType;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;


class CustomEquipeFichierFilter implements FilterInterface
{
    use FilterTrait;

    public static function new(string $propertyName, $label = null): self
    {
        return (new self())
            ->setFilterFqcn(__CLASS__)
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType(CustomEquipeFichierFilterType::class);
    }

    public function apply(QueryBuilder $queryBuilder, FilterDataDto $filterDataDto, ?FieldDto $fieldDto, EntityDto $entityDto): void
    {
        $typefichier = $_REQUEST['typefichier'];
        $queryBuilder
            ->andWhere('entity.equipe =:value')
            ->setParameter('value', $filterDataDto->getValue());
        if ($typefichier == 0) {
            $queryBuilder
                ->andWhere('entity.typefichier<=:type')
                ->setParameter('type', 1);
        }
        if ($typefichier > 1) {
            $queryBuilder
                ->andWhere('entity.typefichier =:type')
                ->setParameter('type', $typefichier);
        }


    }


}