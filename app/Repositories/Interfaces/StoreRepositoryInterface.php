<?php

namespace App\Repositories\Interfaces;

interface StoreRepositoryInterface
{
    public function find($id);
    public function all();
}