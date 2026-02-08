<?php

namespace Erp\HR\App\Http\Controllers;

use Illuminate\Routing\Controller;

class HRController extends Controller
{
    public function index()
    {
        return view('h_r::index');
    }
}
