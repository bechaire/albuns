<?php

namespace App\Interface;

interface ImageFacadeInterface
{
    public function loadImage(string $path): void;
    public function resizeToMaxSide(int $maxSide): void;
    public function rotate(int $degree, string $defaultColor = ''): void;
    public function flipV(): void;
    public function flipH(): void;
    public function strip(): void;
    public function thumb(int $maxSide): void;
    public function saveAsJpeg(int $compress, bool $removeOriginal = false, string $newFilePath = ''): void;
}
