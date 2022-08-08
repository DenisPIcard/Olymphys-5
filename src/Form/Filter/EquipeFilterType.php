<?php

namespace App\Form\Filter;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\FilterType;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\FilterTypeTrait;

//use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Edition;
use App\Entity\Equipesadmin;
use App\Entity\Centrescia;


class EquipeFilterType extends FilterType
{
    use FilterTypeTrait;

    public function filter(QueryBuilder $queryBuilder, FormInterface $form, array $metadata)
    {

        $datas = $form->getParent()->getData();
        $listparam = array();
        if (isset($datas['edition'])) {
            $listparam['edition_'] = $datas['edition'];
        }
        if (isset($datas['centre'])) {
            $centres = $datas['centre'];
            $n = 0;
            foreach ($centres as $centre) {
                $listparam['centre' . $n] = $centre;
                $n++;
            }
            unset($centre);
        }
        //$queryBuilder->expr()->eq();


        if (isset($datas['edition'])) {

            $queryBuilder->Where('entity.edition =:edition')
                ->setParameter('edition', $datas['edition']);
        }
        if (isset($datas['centre'])) {

            $queryBuilder->andWhere('entity.centre =:centre')
                ->setParameter('centre', $datas['centre'])
                ->orderBy('entity.numero', 'ASC');
        }
        /* $listparam=array();
          if(isset($datas['edition'])){
                                $listparam['edition_']=$datas['edition'];
         }
           if(isset($datas['centre'])){
                               $centres = $datas['centre'];
                                $n=0;
                              foreach($centres as $centre){
                                  $listparam['centre'.$n]=$centre;
                                   $n++;}
           unset($centre);
           }
           dump($listparam);
          dump(array_keys($listparam));
           $n=0;
           foreach($listparam as $param){
               dump($param);
           dump(array_keys($listparam)[$n]);
           $queryBuilder->leftJoin('entity.'.substr(array_keys($listparam)[$n],0,strlen(array_keys($listparam)[$n])-1 ), array_keys($listparam)[$n])
                            ->andWhere( array_keys($listparam)[$n].'.'.substr(array_keys($listparam)[$n],0,strlen(array_keys($listparam)[$n])-1).'=:'.array_keys($listparam)[$n]);

           $n++;
           } unset($param);
           //dd();
           $queryBuilder->setParameters($listparam);*/

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



