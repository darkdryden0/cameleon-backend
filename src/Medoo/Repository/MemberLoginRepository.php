<?php

namespace App\Medoo\Repository;

class MemberLoginRepository extends BaseRepository
{
    public function table(): string
    {
        return 'member_login';
    }
}