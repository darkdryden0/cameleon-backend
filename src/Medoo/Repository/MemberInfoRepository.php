<?php

namespace App\Medoo\Repository;

class MemberInfoRepository extends BaseRepository
{
    public function table(): string
    {
        return 'member_info';
    }
}