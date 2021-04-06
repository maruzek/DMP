<?php

namespace App\ValidateImage;

use Symfony\Component\HttpFoundation\File\UploadedFile;

// service, který validuje obrázky

class ValidateImage
{
    public function isImgValid($file): string
    {
        /** @var UploadedFile $file */
        $ext = $file->guessClientExtension();
        if ($ext == "jpg" || $ext == "jpeg") {
            $im = imagecreatefromjpeg($file);
        } elseif ($ext == "png") {
            $im = imagecreatefrompng($file);
        }

        if (isset($im)) {
            $width = imagesx($im);
            $height = imagesy($im);

            unset($im);
        }
        $filesize = $file->getSize();

        if ($ext == "jpg" || $ext == "jpeg" || $ext == "png") {
            if ($filesize <= 2000000) {
                if ($width <= 2000 && $height <= 2000) {
                    return "success";
                } else {
                    return "badsize";
                }
            } else {
                return "toobig";
            }
        } else {
            return "badext";
        }
    }
}
