<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClaseController extends Controller
{
    public function index()
    {
        return view('admin.clases');
    }
}
