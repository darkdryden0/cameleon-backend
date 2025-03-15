<?php

namespace App\Medoo\Repository;
class UploadImagesRepository extends BaseRepository
{
    public function table(): string
    {
        return 'upload_images';
    }
}