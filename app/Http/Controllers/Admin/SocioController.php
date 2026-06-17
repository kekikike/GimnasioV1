<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SocioController extends Controller
{
    public function index()
    {
        return view('admin.socios');
    }
}
