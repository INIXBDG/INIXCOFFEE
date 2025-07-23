<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class salesPribadiController extends Controller
{
    public function index () {
        return view('Crm.myDashboard');
    }
}
