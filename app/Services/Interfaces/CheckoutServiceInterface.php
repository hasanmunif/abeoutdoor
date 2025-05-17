<?php

namespace App\Services\Interfaces;

use Illuminate\Http\Request;

interface CheckoutServiceInterface
{
    public function prepareCheckout(array $cart = []);
    public function prepareDirectCheckout($product);
    public function processCheckout(Request $request);
    public function updateCheckoutItem(Request $request, $productId);
    public function removeCheckoutItem($productId);
    public function calculateTotals(array $items);
}