<?php

namespace App\Http\Controllers\Crm;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class CRMController extends Controller
{
    public function index(){
        return view('crm.dashboard');
    }
}
