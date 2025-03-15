<?php

namespace App\Medoo\Repository;

class CreditHistoryRepository extends BaseRepository
{
    public function table(): string
    {
        return 'credit_history';
    }
}