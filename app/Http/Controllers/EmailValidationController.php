<?php

namespace App\Http\Controllers;

use App\Http\Services\EmailValidationService;
use Exception;
use Illuminate\Http\Request;
use Validator;

class EmailValidationController extends Controller
{
    public function index(Request $request, EmailValidationService $emailValidationService)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => ['required', 'email:rfc,dns,spoof', 'max:255']
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first()
                ], 400);
            }
            if ($request->has('email')) {
                $email = $request->email;
                return response()->json([
                    $emailValidationService->validate($email)
                ]);
            }
            return response()->json([
                'message' => 'Email is required'
            ], 400);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
