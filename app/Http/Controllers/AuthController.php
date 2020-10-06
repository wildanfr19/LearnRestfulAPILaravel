<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use JWTAuth;
use JWTAuthException;
class AuthController extends Controller
{
    public function store(Request $request)
    {
    	$this->validate($request, [
    		'name'=> 'required',
    		'email'=> 'required|email',
    		'password'=> 'required|min:5'

    	]);

    	$name = $request->input('name');
    	$email = $request->input('email');
    	$password = $request->input('password');

    	$user = new User([
    		'name'=> $name,
    		'email'=> $email,
    		'password'=> bcrypt($password)
    	]);

        $credentials = [
            'email' => $email,
            'password' => $password 

        ];

    	if ($user->save()) {

            $token = null;
            try {
                if (!$token = JWTAuth::attempt($credentials)) {
                    return response()->json([
                        'msg' => 'Email or Password are incorrect'

                    ], 400);
                }
            } catch (JWTAuthException $e) {
                return response()->json([
                    'msg' => 'Failed to create token'

                ], 404);
            }


    		$user->signin = [
    			'href'=> 'api/v1/user/signin',
    			'method'=> 'POST',
    			'params'=> 'email, password'

    		];

    		$response = [
    			'msg'=> 'User Created',
    			'user'=> $user,
                'token'=> $token
    		];

    		return response()->json($response, 201);
    	}

    	$response = [
    		'msg'=> 'An Error Occurated',
    	];

    	return response()->json($response, 404);
    }

    public function signin(Request $request)
    {
    	$this->validate($request, [
            'email' => 'required|email',
            'password'=> 'required|min:5'

        ]);

        $email = $request->input('email');
        $password = $request->input('password');

        if ($user = User::where('email', $email)->first()) {
            $credentials = [
                'email' => $email,
                'password' => $password 

            ];

            $token = null;
            try {
                if (!$token = JWTAuth::attempt($credentials)) {
                    return response()->json([
                        'msg' => 'Email or Password are incorrect'

                    ], 400);
                }
            } catch (JWTAuthException $e) {
                return response()->json([
                    'msg' => 'Failed to create token'

                ], 404);
            }

            $response = [
                'msg'=> 'User Signin',
                'user'=> $user,
                'token'=> $token
            ];

            return response()->json($response, 201);
        }

        $response = [
            'msg' => 'An Error Occured'

        ];

        return response()->json($response, 404);
    }
}
