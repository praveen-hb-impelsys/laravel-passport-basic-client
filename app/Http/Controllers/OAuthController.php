<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Socialite;
use app\User;

class OAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('passport')->setScopes(['view-posts', 'view-user'])->redirect();
        // $queries = http_build_query([
        //     'client_id' => config('services.oauth_server.client_id'),
        //     'redirect_uri' => config('services.oauth_server.redirect'),
        //     'response_type' => 'code',
        //     'scope' => 'view-posts'
        // ]);

        // return redirect(config('services.oauth_server.uri') . '/oauth/authorize?' . $queries);
    }

    public function callback(Request $request)
    {
        $providerData = Socialite::driver('passport')->user();
        dd($providerData);
        // $postParams = [
        //     'grant_type' => 'authorization_code',
        //     'client_id' => config('services.oauth_server.client_id'),
        //     'client_secret' => config('services.oauth_server.client_secret'),
        //     'redirect_uri' => config('services.oauth_server.redirect'),
        //     'username' => 'two@demo.com',
        //     'password' => 'password',            
        //     'code' => $request->code
        // ];
        // dd($request->user());

        // $response = Http::post(config('services.oauth_server.uri') . '/oauth/token', $postParams);
        // $response = $response->json();
        // dd($response);

        // $request->user()->token()->delete();
        // $request->user()->token()->create([
        //      'access_token' => $user['access_token'],
        //      'expires_in' => $user['expires_in'],
        //      'refresh_token' => $user['refresh_token']
        //  ]);

        // return redirect('/home');
    }

    public function refresh(Request $request)
    {
        $response = Http::post(config('services.oauth_server.uri') . '/oauth/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $request->user()->token->refresh_token,
            'client_id' => config('services.oauth_server.client_id'),
            'client_secret' => config('services.oauth_server.client_secret'),
            'redirect_uri' => config('services.oauth_server.redirect'),
            'scope' => 'view-posts'
        ]);

        if ($response->status() !== 200) {
            $request->user()->token()->delete();

            return redirect('/home')
                ->withStatus('Authorization failed from OAuth server.');
        }

        $response = $response->json();
        $request->user()->token()->update([
            'access_token' => $response['access_token'],
            'expires_in' => $response['expires_in'],
            'refresh_token' => $response['refresh_token']
        ]);

        return redirect('/home');
    }
}