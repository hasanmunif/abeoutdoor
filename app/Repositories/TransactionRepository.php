<?php

namespace App\Repositories;

use App\Models\Transaction;
use App\Repositories\Interfaces\TransactionRepositoryInterface;

class TransactionRepository implements TransactionRepositoryInterface
{
    public function create(array $data)
    {
        return Transaction::create($data);
    }

    public function find($id)
    {
        return Transaction::with(['product', 'store'])->find($id);
    }

    public function findByTrxId($trxId)
    {
        return Transaction::where('trx_id', $trxId)->first();
    }

    public function generateUniqueTrxId()
    {
        return Transaction::generateUniqueTrxId();
    }

    public function updatePaymentStatus($trxId, $status, $isPaymentSuccess = false)
    {
        $transaction = $this->findByTrxId($trxId);

        if (!$transaction) {
            return null;
        }

        $transaction->payment_status = $status;

        if ($isPaymentSuccess) {
            $transaction->is_paid = true;
            $transaction->status = 'diterima';
        }

        $transaction->save();

        return $transaction;
    }
}