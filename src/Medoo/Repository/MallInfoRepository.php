<?php

namespace App\Medoo\Repository;

class MallInfoRepository extends BaseRepository
{
    public function table(): string
    {
        return 'mall_info';
    }
}