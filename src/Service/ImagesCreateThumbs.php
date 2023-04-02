<?php

namespace App\Service;


use App\Entity\Odpf\OdpfImagescarousels;
use App\Entity\Odpf\OdpfLogos;
use App\Entity\Photos;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp;

use Exception;
use Imagick;
use ImagickException;
use PhpParser\Node\Scalar\String_;
use Symfony\Component\HttpFoundation\File\UploadedFile;


class ImagesCreateThumbs
{

    /**
     * @throws ImagickException
     */
    public function createThumbs($image)
    {   set_time_limit(60);
        if (($image instanceof OdpfImagescarousels) or ($image instanceof OdpfLogos)) {
            if ($image instanceof OdpfImagescarousels) {
                $path = 'odpf/odpf-images/imagescarousels/';
            }
            if ($image instanceof OdpfLogos) {

                $path = 'odpf/odpf-logos/';
            }
            if (!file_exists($path . 'tmp')) {
                mkdir($path . 'tmp');
            };
            $fileImage = $image->getImageFile();
            $imagetmp = new Imagick($fileImage);

            if ($image->getCarousel() !== null) {
                $image->getCarousel()->getBlackbgnd() == false ? $fond = new Imagick('images/fond_blanc_carousel.jpg') : $fond = new Imagick('images/fond_noir_carousel.jpg');

            }
            try {
                $imagetmp->readImage($path . $image->getName());
                $heightOrig = $imagetmp->getImageHeight();
                $widthOrig = $imagetmp->getImageWidth();
                $properties = $imagetmp->getImageProperties();
                if (isset($properties['exif:Orientation'])) {

                    if ($properties['exif:Orientation'] == 8) {//(270°)
                        $heightOrig = $imagetmp->getImageWidth();
                        $widthOrig = $imagetmp->getImageHeight();
                        $imagetmp->rotateImage('#000', -90);
                    }
                    if ($properties['exif:Orientation'] == 6) {
                        $heightOrig = $imagetmp->getImageWidth();
                        $widthOrig = $imagetmp->getImageHeight();
                        $imagetmp->rotateImage('#000', 90);
                    }
                }

                $percent = 200 / $heightOrig;
                $nllwidth = $widthOrig * $percent;
                $nllheight = 200;

                if ($widthOrig * $percent <= 230) {
                    $imagetmp->resizeImage($nllwidth, $nllheight, imagick::FILTER_LANCZOS, 1);

                    $y = 0;//$imagetmp->writeImage($fileImage);
                }
                if ($widthOrig * $percent > 230) {
                    $nllwidth = 230;
                    $nllheight = $heightOrig * 230 / $widthOrig;
                    $y = (200 - $nllheight) / 2;
                }
                $x = (230 - $nllwidth) / 2;

                $imagetmp->resizeImage($nllwidth, $nllheight, imagick::FILTER_LANCZOS, 1);
                if ($image->getImageFile()->getExtension() == 'gif') {

                    $fond->compositeImage($imagetmp, imagick::COMPOSITE_OVER, $x, $y);
                }

                if ($image->getImageFile()->getExtension() == 'png') {

                    $fond->compositeImage($imagetmp, imagick::COMPOSITE_OVER, $x, $y);
                }
                if (($image->getImageFile()->getExtension() == 'jpg') or ($image->getImageFile()->getExtension() == 'jpeg') or ($image->getImageFile()->getExtension() == 'JPG')) {

                    $formatCouleur = $imagetmp->getImageColorspace();

                    if (($formatCouleur == imagick::COLORSPACE_CMYK) or ($formatCouleur == imagick::COLORSPACE_CMY)) {
                        $imagetmp->transformImageColorspace(imagick::COLORSPACE_RGB);

                    }
                    $fond->compositeImage($imagetmp, imagick::COMPOSITE_OVER, $x, $y);
                    $fond->setColorspace(imagick::COLORSPACE_RGB);
                    $fond->setFormat('jpg');

                }

                $fond->writeImage($fileImage);
            } catch (\Exception $e) {


            }
        }


        if ($image instanceof Photos) {
            $path = 'odpf/odpf-archives/' . $image->getEditionspassees()->getEdition() . '/photoseq/';
            $pathThumb = $path . 'thumbs/';
            $nomPhoto= $image->getPhoto();
            if (file_exists($path .$nomPhoto)){
                $fileImage = $image->getPhotoFile();
                $imageOrig = new Imagick($fileImage);
                $imageOrig->readImage($path . $image->getPhoto());
                $properties = $imageOrig->getImageProperties();
                $heightOrig = $imageOrig->getImageHeight();
                $widthOrig = $imageOrig->getImageWidth();
                if (isset($properties['exif:Orientation'])) {

                    if ($properties['exif:Orientation'] == 8) {//(270°)
                        $heightOrig = $imageOrig->getImageWidth();
                        $widthOrig = $imageOrig->getImageHeight();
                        $imageOrig->rotateImage('#000', -90);
                    }
                    if ($properties['exif:Orientation'] == 6) {
                        $heightOrig = $imageOrig->getImageWidth();
                        $widthOrig = $imageOrig->getImageHeight();
                        $imageOrig->rotateImage('#000', 90);
                    }
                }
                $percent = 200 / $heightOrig;
                $nllwidth = $widthOrig * $percent;
                $nllheight = 200;
                $fond = new Imagick('images/fond_noir_carousel.jpg');
                $formatCouleur = $imageOrig->getImageColorspace();
                $y = (200 - $nllheight) / 2;

                $x = (230 - $nllwidth) / 2;
                if (($formatCouleur == imagick::COLORSPACE_CMYK) or ($formatCouleur == imagick::COLORSPACE_CMY)) {
                    $imageOrig->transformImageColorspace(imagick::COLORSPACE_RGB);
                }
                if ($widthOrig * $percent <= 230) {
                    $imageOrig->resizeImage($nllwidth, $nllheight, imagick::FILTER_LANCZOS, 1);
                    $fond->compositeImage($imageOrig, imagick::COMPOSITE_OVER, $x, $y);
                }

                if ($widthOrig * $percent > 230) {
                    $nllwidth = 230;
                    $nllheight = $heightOrig * 230 / $widthOrig;
                    $y = (200 - $nllheight) / 2;
                    $x = (230 - $nllwidth) / 2;
                    $imageOrig->resizeImage($nllwidth, $nllheight, imagick::FILTER_LANCZOS, 1);
                }
                $fond->compositeImage($imageOrig, imagick::COMPOSITE_OVER, $x, $y);
                $fond->setColorspace(imagick::COLORSPACE_RGB);
                $fond->writeImage($pathThumb . $image->getPhoto());
                }
            }
        }

}