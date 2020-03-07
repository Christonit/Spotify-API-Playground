<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use GuzzleHttp\Client;

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

        $access_token =  $request->code;

        // $client = new Client();
        $client = new Client([
            'base_uri' => 'https://accounts.spotify.com'
        ]);
        $redirect_url = urlencode('http://127.0.0.1:8000/callback');

        $code = base64_encode(env('SPOTIFY_CLIENT_ID').':'.env('SPOTIFY_SECRET_ID'));
        // $code = env('SPOTIFY_CLIENT_ID').':'.env('SPOTIFY_SECRET_ID');

        // dd($code);

        // $header = ['Authorization' => 'Basic '.$code];
        $body = ['grant_type'=>'authorization_code','code'=>$access_token,'redirect_uri'=>$redirect_url];

        // dd(['client_id'=>env('SPOTIFY_CLIENT_ID'),'client_secret'=>env('SPOTIFY_SECRET_ID')]);
        $response = $client->request('POST','/api/token',[
            'form_params' => [
                'grant_type' => 'authorization_code',
                'code' => $access_token,
                'redirect_uri' => 'http://127.0.0.1:8000/callback',
                'client_id'=>env('SPOTIFY_CLIENT_ID'),
                'client_secret'=>env('SPOTIFY_SECRET_ID')
                ]
            ]);
        $tokens = $response->getBody();
        return json_decode($tokens, true);

    }
    public function spotify()
    {

        $scopes = 'user-read-private user-read-email';
        $redirect_url = 'http://127.0.0.1:8000/callback';

        return redirect('https://accounts.spotify.com/authorize'.
        '?response_type=code'.
        '&client_id='.env('SPOTIFY_CLIENT_ID').
        ($scopes ? '&scope='.urlencode($scopes) : '').
        '&redirect_uri='.urlencode($redirect_url));

    }
}
