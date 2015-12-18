<?php

namespace Greg\System;

class Image extends File
{
    public function type()
    {
        return $this->typeFile($this->file());
    }

    public function width()
    {
        return $this->widthFile($this->file());
    }

    public function height()
    {
        return $this->heightFile($this->file());
    }

    public function is()
    {
        return $this->isFile($this->file());
    }

    public function get()
    {
        return $this->getFile($this->file());
    }

    public function saveJPEG($image, $fixDir = false, $quality = 75)
    {
        return $this->saveJPEGFile($image, $this->file(), $fixDir, $quality);
    }

    public function saveGIF($image, $fixDir = false)
    {
        return $this->saveGIFFile($image, $this->file(), $fixDir);
    }

    public function savePNG($image, $fixDir = false, $quality = 9, $filters = PNG_NO_FILTER)
    {
        return $this->savePNGFile($image, $this->file(), $fixDir, $quality, $filters);
    }

    static public function typeFile($file)
    {
        $type = function_exists('exif_imagetype') ? @exif_imagetype($file) : null;

        if (!$type) {
            list($width, $height, $type) = @getimagesize($file);

            unset($width, $height);

            return $type;
        }

        return $type;
    }

    static public function typeToExt($type, $point = true)
    {
        return image_type_to_extension($type, $point);
    }

    static public function extFile($file, $point = false)
    {
        $type = static::typeFile($file);

        $ext = static::typeToExt($type, false);

        switch($ext) {
            case 'jpeg':
                $ext = 'jpg';

                break;
            case 'tiff':
                $ext = 'tif';

                break;
        }

        if ($point) {
            $ext = '.' . $ext;
        }

        return $ext;
    }

    static public function widthFile($file)
    {
        list($width) = @getimagesize($file);

        if (!$width) {
            $width = imagesx(static::getFile($file));
        }

        return $width;
    }

    static public function heightFile($file)
    {
        list($width, $height) = @getimagesize($file);

        unset($width);

        if (!$height) {
            $height = imagesy(static::getFile($file));
        }

        return $height;
    }

    static public function isFile($file)
    {
        return static::typeFile($file) ? true : false;
    }

    static public function mimeFile($file)
    {
        return static::typeToMime(static::typeFile($file));
    }

    static public function typeToMime($type)
    {
        return image_type_to_mime_type($type);
    }

    static public function getFile($file)
    {
        $image = null;

        switch(static::typeFile($file)) {
            case IMAGETYPE_GIF:
                $image = imagecreatefromgif($file);

                break;
            case IMAGETYPE_JPEG:
                $image = imagecreatefromjpeg($file);

                break;
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng($file);

                break;
        }

        if (!$image) {
            $image = imagecreatefromstring(file_get_contents($file));

            if (!$image) {
                throw new \Exception('Wrong file type.');
            }
        }

        return $image;
    }

    static public function saveJPEGFile($image, $file, $fixDir = false, $quality = 75)
    {
        $fixDir && static::fixFileDir($file, true);

        imagejpeg($image, $file, $quality);

        return true;
    }

    static public function getJPEG($image, $quality = 75)
    {
        ob_start();

        imagejpeg($image, null, $quality);

        return ob_get_clean();
    }

    static public function saveGIFFile($image, $file, $fixDir = false)
    {
        $fixDir && static::fixFileDir($file, true);

        imagegif($image, $file);

        return true;
    }

    static public function getGIF($image)
    {
        ob_start();

        imagegif($image, null);

        return ob_get_clean();
    }

    static public function savePNGFile($image, $file, $fixDir = false, $quality = 9, $filters = PNG_NO_FILTER)
    {
        $fixDir && static::fixFileDir($file, true);

        imagepng($image, $file, $quality, $filters);

        return true;
    }

    static public function getPNG($image, $quality = 9, $filters = PNG_NO_FILTER)
    {
        ob_start();

        imagepng($image, null, $quality, $filters);

        return ob_get_clean();
    }
}