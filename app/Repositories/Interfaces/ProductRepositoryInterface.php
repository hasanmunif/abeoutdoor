<?php

namespace App\Repositories\Interfaces;

interface ProductRepositoryInterface
{
    public function find($id);
    public function findBySlug($slug);
    public function decreaseStock($product, $quantity);
}