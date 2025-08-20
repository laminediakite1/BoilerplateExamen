<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\Product;

class ProductsController extends BaseController
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('role:admin');
    }

    /**
     * Get the entity configuration
     */
    protected function getEntityConfig(): array
    {
        return config('entities.products');
    }
}