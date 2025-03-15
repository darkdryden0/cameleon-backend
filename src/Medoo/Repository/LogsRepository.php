<?php

namespace App\Medoo\Repository;

class LogsRepository extends BaseRepository
{
    public function table(): string
    {
        return 'logs';
    }
}