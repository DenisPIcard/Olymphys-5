<?php

namespace App\Controller\Admin\Field;


use App\Entity\Equipes;
use App\Entity\Equipesadmin;
use App\Entity\Jures;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use phpDocumentor\Reflection\Types\Collection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class JureTypeField implements FieldInterface
{
    use FieldTrait;

    private EntityManagerInterface $em;


    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

    }


    public static function new(string $propertyName, $label = null): self
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)

            // this template is used in 'index' and 'detail' pages
            ->setTemplatePath('admin/field/map.html.twig')

            // this is used in 'edit' and 'new' pages to edit the field contents
            // you can use your own form types too
            ->setFormType(TextareaType::class)
            ->addCssClass('field-map')

            // loads the CSS and JS assets associated to the given Webpack Encore entry
            // in any CRUD page (index/detail/edit/new). It's equivalent to calling
            // encore_entry_link_tags('...') and encore_entry_script_tags('...')
            ->addWebpackEncoreEntries('admin-field-map')

            // these methods allow to define the web assets loaded when the
            // field is displayed in any CRUD page (index/detail/edit/new)
            ->addCssFiles('js/admin/field-map.css')
            ->addJsFiles('js/admin/field-map.js');
    }
    /* $jure = $options['jure'];
     $qb = $this->em->getRepository(Equipes::class)->createQueryBuilder('e');
     $repositoryJure = $this->em->getRepository(Jures::class);
     $equipesExaminees = $repositoryJure->getAttribution($jure);

     foreach ($equipesExaminees as $attrib) {

         $lettre = key($attrib);
         $equipe = $this->em->getRepository(Equipes::class)->findOneBy(['equipeinter.lettre' => $lettre]);

         $builder = $builder
             ->add('attrib', ChoiceType::class, [
                 'choices' => ['null' => '',
                     '0' => 'E',
                     '1' => 'L'],
                 'data' => $attrib
             ]);


 } }*/


}