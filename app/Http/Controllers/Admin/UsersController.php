<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\User;
use Illuminate\Http\Request;

class UsersController extends BaseController
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('role:admin')->except(['show']);
    }

    /**
     * Get the entity configuration
     */
    protected function getEntityConfig(): array
    {
        return config('entities.users');
    }
}