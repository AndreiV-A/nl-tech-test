<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
		$data = $request->validate([
			'email' => 'required|unique:users',
			'password' => 'required|min:8',
		]);
		
		return new UserResource(User::create([
			'name' => $data['email'],
			'email' => $data['email'],
			'password' => bcrypt($data['password']),
		]));
	}
}
