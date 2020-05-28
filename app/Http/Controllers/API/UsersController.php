<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\User;

class UsersController extends Controller
{
    public function index(){
        return UserResource::collection(User::paginate(10));
    }

	public function show(User $user){
    	return new UserResource($user);
	}

	public function store(Request $request){
		$validated_data = $request->validate([
			'email' => 'email|required|unique:users',
			'password' => 'required|min:8',
		]);
		$validated_data['name'] = $validated_data['email'];
		$validated_data['password'] = Hash::make($validated_data['password']);

		$user = User::create($validated_data);
		$accessToken = $user->createToken('authToken')->accessToken;
		
		return response([
			'user' => new UserResource($user),
			'access_token' => $accessToken,
		]);


	}
}
