<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class APIController extends Controller
{
    public function usage(){
        $user = Auth::user();
        
    }
}
