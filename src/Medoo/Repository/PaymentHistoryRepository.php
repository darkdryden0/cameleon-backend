<?php
namespace App\Medoo\Repository;

class PaymentHistoryRepository extends BaseRepository
{
    public function table(): string
    {
        return 'payment_history';
    }
}