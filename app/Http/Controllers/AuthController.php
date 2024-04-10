<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use PSpell\Config;

class AuthController extends Controller
{
    //
    public function userLogin(Request $request)
    {
        try {
            $state = Str::random(40);
            $request->session()->put("state", $state);
            $clientId = env('CLIENT_ID');
            $redirect_url = config('services.sso.redirect_url');
            $query = http_build_query([
                "client_id" => $clientId,
                "redirect_uri" => $redirect_url . "/callback",
                "response_type" => "code",
                "scope" => "*",
                "state" => $state,
            ]);
            $base_url = config('services.sso.base_url');
            return redirect($base_url . "/oauth/authorize?" . $query);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred. Please try again later.'], 500);
        }
    }
    public function callback(Request $request)
    {
        $state = $request->state;
        abort_if(strlen($state) == 0 || $state != $request->state, 400, 'Invalid state');
        $clientId = config('services.sso.client_id');
        $clientSecret = config('services.sso.client_secret');
        $password = $request->session()->get('password');
        $base_url = config('services.sso.base_url');
        $response = Http::asForm()->post($base_url . "/oauth/token", [
            "grant_type" => "password",
            "client_id" =>  $clientId,
            "client_secret" => $clientSecret,
        ]);
        session(['oauth_response' => $response->json()]);
        $accessToken = $response->json()['access_token'];
        session(['access_token' => $accessToken]);
        return redirect("/authuser");
    }
    public function authUser(Request $request)
    {
        $access_token = session('access_token');
        $password = session('password');
        $base_url = config('services.sso.base_url');
        $response = Http::withHeaders([
            "Accept" => "application/json",
            "Authorization" => "Bearer " . $access_token
        ])->get($base_url . "/api/user");

        $userData = $response->json();

        $email = $userData['email'];
        $name = $userData['first_name'] . ' ' . $userData['last_name'];
        // dd($name);
        $user = User::where('email', $email)->first();
        if (!$user) {
            User::insert([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
            ]);
        }
        $user->assignRole('User');
        // if ($agentRole && !$user->roles->contains($agentRole->id)) {
        //     $user->roles()->attach($agentRole->id);
        // }
        $userType = $userData['user_type'];
        if ($userType == 0 || $userType == 1) {
            return $this->sendLoginResponse($request);
        }
    }
    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();

        $this->clearLoginAttempts($request);

        if ($response = $this->authenticated($request, $this->guard()->user())) {
            return $response;
        }

        return $request->wantsJson()
            ? new JsonResponse([], 204)
            : redirect()->intended($this->redirectPath());
    }
}
