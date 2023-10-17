<?php


namespace App\Controller\Form;


use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use Vich\UploaderBundle\Form\Type\VichFileType;

class VichFilesField implements FieldInterface


{
    use FieldTrait;

    public const OPTION_BASE_PATH = 'basePath';
    public const OPTION_UPLOAD_DIR = 'uploadDir';
    public const OPTION_UPLOADED_FILE_NAME_PATTERN = 'uploadedFileNamePattern';


    public static function new(string $propertyName, ?string $label = null): VichFilesField
    {
        return (new self())
            ->setProperty($propertyName)
            ->setTemplatePath('')
            ->setLabel($label)
            ->setCustomOption(self::OPTION_BASE_PATH, null)
            ->setFormType(VichFileType::class);
    }

    public function setBasePath(string $path): self
    {
        $this->setCustomOption(self::OPTION_BASE_PATH, $path);

        return $this;
    }

}