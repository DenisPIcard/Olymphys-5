<?php

namespace App\Controller\OdpfAdmin;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use FOS\CKEditorBundle\Form\Type\CKEditorType;

class AdminCKEditorField implements FieldInterface
{

    use FieldTrait;

    public static function new(string $propertyName, ?string $label = null, ?string $configName = null): self
    {


        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)
            // this template is used in 'index' and 'detail' pages
            ->setTemplatePath('@EasyAdmin/crud/field/text_editor.html.twig')
            // this is used in 'edit' and 'new' pages to edit the field contents
            // you can use your own form types too
            ->setFormType(CKEditorType::class)
            ->setFormTypeOptions(
                [
                    'config' => [
                        'toolbar' => 'full',
                        //'filebrowserUploadRoute' => 'post_ckeditor_image',
                        //'filebrowserUploadRouteParameters' => ['slug' => 'image'],
                        'extraPlugins' => 'templates',
                        'rows' => '20',

                    ],
                    'attr' => ['rows' => '20'],

                ])
            ->addCssClass('field-ck-editor');
    }

}