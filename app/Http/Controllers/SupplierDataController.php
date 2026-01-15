<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class SupplierDataController extends Controller
{
    public function index(){
        return Inertia::render('supplierData/Index');
    }
}
