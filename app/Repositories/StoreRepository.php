<?php

namespace App\Repositories;

use App\Models\Store;
use App\Repositories\Interfaces\StoreRepositoryInterface;

class StoreRepository implements StoreRepositoryInterface
{
    public function find($id)
    {
        return Store::find($id);
    }

    public function all()
    {
        return Store::all();
    }
}