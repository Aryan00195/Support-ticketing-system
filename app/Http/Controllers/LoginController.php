<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\log;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    //
    public function  check(Request $request)
    {
        $credential = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
   
        
        try {
            
            if (Auth::attempt($credential)) {
                $user = Auth::user();
                if ($user) {
                    // dd($user->getRoleNames());
                    // dd($user->hasRole('User'));
                    if ($user->hasRole('User')) {
                        return redirect('/user/ticket');
                        // dd($user);
                    } else if ($user->hasRole('Agent')) {
                        return redirect('/agent/ticket');
                    } else {
                        return view('admin.adminpanel');
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
    public function logout()
    {
        Auth::logout();
        return response()->json(['status' => true, 'message' => 'Logout Successfully'], 200);
    }
}