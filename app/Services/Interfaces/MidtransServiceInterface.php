<?php

namespace App\Services\Interfaces;

use Illuminate\Http\Request;

interface MidtransServiceInterface
{
    public function processMidtransPayment(Request $request);
    public function handleMidtransCallback(Request $request);
    public function generateSnapToken(array $params);
}