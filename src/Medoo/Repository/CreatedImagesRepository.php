<?php

namespace App\Medoo\Repository;
class CreatedImagesRepository extends BaseRepository
{
    public function table(): string
    {
        return 'created_images';
    }
}