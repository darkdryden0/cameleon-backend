<?php

namespace App\Medoo\Repository;
class ReferenceImagesRepository extends BaseRepository
{
    public function table(): string
    {
        return 'reference_images';
    }
}