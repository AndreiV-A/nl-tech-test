<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\User;

class AuthController extends Controller
{
   public function login(Request $request){

		// $http = new \GuzzleHttp\Client;		
		// try {
		// 	$response = $http->post(config('services.passport.login_endpoint'), [
		// 		'form_params' => [
		// 			'grant_type' => 'password',
		// 			'client_id' => config('services.passport.client_id'),
		// 			'client_secret' => config('services.passport.client_secret'),
		// 			'username' => $request->username,
		// 			'password' => $request->password,
		// 		]
		// 	]);			
		// 	return $response->getBody();		
		// } catch (\GuzzleHttp\Exception\BadResponseException $e) {			
		// 	switch ($code = $e->getCode()){
		// 		case 400:
		// 			$message = 'Invalid Request. Needs username and password.';
		// 			break;
		// 		case 401:
		// 			$message = 'Wrong credentials.';
		// 			break;
		// 		default:
		// 			$mesage = 'Something went wrong.';
		// 	}
		// 	return response()->json(['message' => $message], $code);			
		// }

		$validated_data = $request->validate([
			'email' => 'email|required',
			'password' => 'required',
		]);

		if (!auth()->attempt($validated_data)){
			return response()->json(['message' => 'Invalid creds'], 401);
		}

		$accessToken = auth()->user()->createToken('authToken')->accessToken;

		return response([
			'access_token' => $accessToken,
		]);
	}
	
	public function logout(){
		auth()->user()->tokens->each(function ($token, $key) {
			print('deleted.');
			$token->delete();
		});
		
		return response()->json('Logged out successfully', 200);
	}
}
