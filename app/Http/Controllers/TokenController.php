<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GetStream\StreamChat\Client as StreamClient;

class TokenController extends Controller
{
    public function generate(Request $request)
    {
        $client = new StreamClient(
            env('MIX_STREAM_API_KEY'),
            env('MIX_STREAM_API_SECRET'),
            null,
            null,
            9
        );

        return response()->json([
            'token' => $client->createToken($request->username)
        ]);
    }
}
