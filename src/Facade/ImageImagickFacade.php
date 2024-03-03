<?php

declare(strict_types=1);

namespace App\Facade;

use Imagick;
use App\Interface\ImageFacadeInterface;
use Symfony\Component\Filesystem\Filesystem;

class ImageImagickFacade implements ImageFacadeInterface
{
    private \Imagick $image;
    private string $originalImagePath;

    public function __construct(
        private Filesystem $filesystem
    ) {
        $this->image = new \Imagick();
    }

    public function loadImage(string $path): void
    {
        $this->originalImagePath = $path;
        $this->image->readImage($path);
    }

    public function resizeToMaxSide(int $maxSide): void
    {
        if ($maxSide < 1) {
            return;
        }
        
        // se estou pendindo pra redimensionar uma imagem que é menor do que eu quero
        // então o maior tamanho da imagem será o dela, ou seja, apenas recompacta
        if ($maxSide > $this->image->getImageWidth() && $maxSide > $this->image->getImageHeight()) {
            $maxSide = max($this->image->getImageWidth(), $this->image->getImageHeight());
        }

        $this->image->resizeImage($maxSide, $maxSide, Imagick::FILTER_LANCZOS, 1, true);
    }

    public function rotate(int $degree, string $defaultColor = '#FFFFFF'): void
    {
        $degree = ($degree < 1 || $degree > 360) ? 0 : $degree;
        $this->image->rotateImage($defaultColor, $degree);
    }

    public function flipV(): void
    {
        $this->image->flipImage();
    }

    public function flipH(): void
    {
        $this->image->flopImage();
    }

    public function strip(): void
    {
        $this->image->stripImage();
    }

    public function thumb(int $maxSide): void
    {
        if ($maxSide < 1) {
            return;
        }

        // se estou pendindo pra redimensionar uma imagem que é menor do que eu quero
        // apenas removo os metadados
        if ($maxSide > $this->image->getImageWidth() && $maxSide > $this->image->getImageHeight()) {
            $this->image->stripImage();
            return;
        }

        $this->image->thumbnailImage($maxSide, $maxSide, true);
    }

    public function saveAsJpeg(int $compress, bool $removeOriginal = false, string $newFilePath = ''): void
    {
        $this->image->setImageFormat('jpeg');
        $this->image->setImageCompression(Imagick::COMPRESSION_JPEG);
        $this->image->setImageCompressionQuality($compress);

        if ($removeOriginal) {
            $this->filesystem->remove($this->originalImagePath);
        }

        if (!$newFilePath) {
            $originalFile = \pathinfo($this->originalImagePath);
            $newFilePath = $originalFile['dirname'] . '/' . $originalFile['filename'] . '.jpg';
        }

        $this->image->autoOrient();

        $this->image->writeImage($newFilePath);
    }
}
