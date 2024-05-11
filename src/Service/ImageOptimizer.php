<?php

declare(strict_types=1);

namespace App\Service;

use Imagine\Gd\Imagine;
use Imagine\Image\Box;

class ImageOptimizer
{
    private const MAX_WIDTH = 200;
    private const MAX_HEIGHT = 150;



    public function __construct(private Imagine $imagine)
    {

    }

    public function resize(string $filePath)
    {

        list($imageWidth, $imageHeight) = getimagesize($filePath);
        $ratio = $imageWidth / $imageHeight;
        $width = self::MAX_WIDTH;
        $height = self::MAX_HEIGHT;
        if ($width / $height > $ratio) {
            $width = $height * $ratio;
        } else {
            $height = $width / $ratio;
        }

        $photo = $this->imagine->open($filePath);
        $photo->resize(new Box($width, $height))->save($filePath);
    }
}
