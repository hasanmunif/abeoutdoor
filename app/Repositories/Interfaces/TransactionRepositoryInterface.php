<?php

namespace App\Repositories\Interfaces;

interface TransactionRepositoryInterface
{
    public function create(array $data);
    public function find($id);
    public function findByTrxId($trxId);
    public function generateUniqueTrxId();
    public function updatePaymentStatus($trxId, $status, $isPaymentSuccess = false);
}