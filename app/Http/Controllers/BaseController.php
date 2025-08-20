<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\Backend\CrudTrait;
use App\Traits\Backend\ExportTrait;
use App\Traits\Backend\BulkActionTrait;

abstract class BaseController extends Controller
{
    use CrudTrait, ExportTrait, BulkActionTrait;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Get the entity configuration
     */
    abstract protected function getEntityConfig(): array;
}