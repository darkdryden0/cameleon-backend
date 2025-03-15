<?php

namespace App\Medoo\Repository;
class StorageImagesRepository extends BaseRepository
{
    public function table(): string
    {
        return 'storage_images';
    }
}