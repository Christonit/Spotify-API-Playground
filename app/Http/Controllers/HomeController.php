<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use GuzzleHttp\Client;
use Psr\Http\Message\ServerRequestInterface;
use App\User;
use Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }
    public function callback(Request $request)
    {

        // 3. Save access token
        $access_token =  $request->code;

        // 4.  OPen new client in guzzle to accounts.spotify
        $client = new Client([
            'base_uri' => 'https://accounts.spotify.com'
        ]);

        $redirect_url = 'http://127.0.0.1:8000/auth/spotify/callback';

        $code = base64_encode(env('SPOTIFY_CLIENT_ID').':'.env('SPOTIFY_SECRET_ID'));

        // 5. Send post to Api token to get access token
        $response = $client->request('POST','/api/token',[
            'form_params' => [
                'grant_type' => 'authorization_code',
                'code' => $access_token,
                'redirect_uri' => $redirect_url,
                'client_id'=>env('SPOTIFY_CLIENT_ID'),
                'client_secret'=>env('SPOTIFY_SECRET_ID')
                ]
            ]);


        // 6. encode response in JSON  
        $tokens = json_decode($response->getBody(),true);

        // 7. OPen get request to api spotify to obtain user ID.
        $user_client = new Client(['base_uri' => 'https://api.spotify.com']);
        
        $user_client = $user_client->request('GET',"/v1/me",
        [
            'headers' => ['Authorization'=> 'Bearer '.$tokens['access_token']]
        ]);
        $spotify_user = json_decode($user_client->getBody(),true);

        $spotify_user['access_token'] = $tokens['access_token'];
        $spotify_user['refresh_token'] = $tokens['refresh_token'];

        
        // return $user['email'];
        $user = $this->findOrCreateSpotifyUser($spotify_user);
        
        auth()->login($user);
        return redirect('/');



    }
    public function authSpotify()
    {

        // 1. Set scopes of the privilege you want.
        $scopes = 'user-read-private user-read-email';
        $redirect_url = 'http://127.0.0.1:8000/auth/spotify/callback';


        // 2. Send Secret and CLient ID to Spotify server.

        return redirect('https://accounts.spotify.com/authorize'.
                '?response_type=code'.
                '&client_id='.env('SPOTIFY_CLIENT_ID').
                ($scopes ? '&scope='.urlencode($scopes) : '').
                '&redirect_uri='.urlencode($redirect_url));

    }

    protected function findOrCreateSpotifyUser($spotify){

        
        $user = User::firstOrNew(['spotify_id' => $spotify['id'] ]);

        if( $user->exists){

            $user->access_token = $spotify['access_token'];
            $user->refresh_token = $spotify['refresh_token'];
            $user->save();

            return $user;
        }

        $user->fill([
            'spotify_id' => $spotify['id'],
            'spotify_email' => $spotify['email'],
            'avatar' => $spotify['images'][0]['url'],
            'access_token' => $spotify['access_token'],
            'refresh_token' => $spotify['refresh_token']
        ])->save();

        return $user;

    }

    public function authLogout(){
        Auth::logout();

        return redirect('/');
    }
}
