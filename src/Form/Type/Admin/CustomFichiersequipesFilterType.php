<?php


namespace App\Form\Type\Admin;

use App\Entity\Fichiersequipes;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

//use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


class CustomFichiersequipesFilterType extends AbstractType
{

    private EntityManagerInterface $doctrine;

    public function __construct(RequestStack $requestStack, EntityManagerInterface $doctrine)
    {

        $this->doctrine = $doctrine;
    }

    public function configureOptions(OptionsResolver $resolver)

    {
        $choices = [
            'mémoires' => 0,
            'annexes' => 1,

        ];

        // $edition = $_SESSION['_sf2_attributes']['edition'];
        $resolver->setDefaults([
            'choices' => $choices
        ]);

    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }


}
