<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
                    if ($user->hasRole('User')) {
                        return redirect('/user/ticket');
                    } else if ($user->hasRole('Agent')) {
                        return view('agent.agentpanel');
                    } else {
                        return redirect('/admin/panel');
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
        try {
            Auth::logout();
            return response()->json(['status' => true, 'message' => 'Logout Successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
