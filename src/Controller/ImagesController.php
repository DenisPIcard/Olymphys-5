<?php

namespace App\Controller;


use App\Entity\Odpf\OdpfImagescarousels;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\ParameterBag;

class ImagesController extends AbstractController
{
    public function createThumbs(OdpfImagesCarousels $image)
    {

        $this->params = new ParameterBag();
        $headers = exif_read_data($image->getImageFile());
        $width_orig = $headers['ExifImageWidth'];
        $height_orig = $headers['ExifImageLength'];
        $imagejpg = imagecreatefromjpeg($image->getImageFile());

        if (isset($headers['Orientation'])) {
            if (($headers['Orientation'] == '6') and ($width_orig > $height_orig)) {
                $image = imagerotate($imagejpg, 270, 0);
                $widthtmp = $width_orig;
                $width_orig = $height_orig;
                $height_orig = $widthtmp;
            }
            if (($headers['Orientation'] == '8') and ($width_orig > $height_orig)) {
                $image = imagerotate($image, 90, 0);
                $widthtmp = $width_orig;
                $width_orig = $height_orig;
                $height_orig = $widthtmp;
            }
        }
        if ($height_orig / $width_orig < 0.866) {
            $width_opt = $height_orig / 0.866;
            $Xorig = ($width_orig - $width_opt) / 2;
            $Yorig = 0;
            $image_opt = imagecreatetruecolor($width_opt, $height_orig);
            imagecopy($image_opt, $imagejpg, 0, 0, $Xorig, $Yorig, $width_opt, $height_orig);
            $width_orig = $width_opt;
        } else {
            $image_opt = $imagejpg;
        }
        $dim = max($width_orig, $height_orig);
        $percent = 200 / $height_orig;
        $new_width = $width_orig * $percent;
        $new_height = $height_orig * $percent;
        $thumb = imagecreatetruecolor($new_width, $new_height);


        $paththumb = 'odpf-images/imagescarousels/' . $image->getName();

        imagecopyresampled($thumb, $image_opt, 0, 0, 0, 0, $new_width, $new_height, $width_orig, $height_orig);
        imagejpeg($thumb, $paththumb);


    }
}
