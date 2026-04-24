<?php

namespace App\Http\Controllers;

use App\Models\Validation;
use Auth;
use Illuminate\Http\Request;

class APIController extends Controller
{
    public function usage(){
        $user = Auth::user();
        $calls = Validation::where('user_id', $user->id)->with('email')->paginate(10);
        return view('pages.usage', [
            'user' => $user,
            'calls' => $calls,
        ]);
    }
}
