<?php

namespace Samik\LaravelAdmin\Http\Controllers\Api;

use Samik\LaravelAdmin\Http\Controllers\Controller;
use Illuminate\Http\Request;
use JWTAuth;

use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\JWTException;

use Samik\LaravelAdmin\Models\User;

class AuthController extends Controller
{
    //
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'phone' => ['required'],
            'password' => ['required'],
            'role_id' => ['required'],
        ]);

        if(!$token = auth('api')->attempt($credentials + ['active' => 1])) {
            return response()->json(['message' => 'Login failed. Invalid credentials or user deactivated.'], 401);
        }

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ]);
    }

    public function refresh(Request $request)
    {
        try {
            $tokenValid = JWTAuth::parseToken()->check();
            if($tokenValid) {
                return response()->json(['message' => 'Token has not expired'], 400);
            }
            else {
                return response()->json([
                    'access_token' => auth('api')->refresh(),
                    'token_type' => 'bearer',
                    'expires_in' => auth('api')->factory()->getTTL() * 60,
                ]);
            }
        }
        catch(TokenExpiredException $ex) {
            return response()->json([
                'access_token' => auth('api')->refresh(),
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60,
            ]);
        }
        catch(TokenBlacklistedException $ex) {
            return response()->json(['message' => 'Token has been blacklisted or already refreshed'], 400);
        }
        catch(JWTException $ex) {
            return response()->json(['message' => 'Token not found'], 400);
        }
    }

    public function logout(Request $request)
    {
        auth('api')->logout();
        return response()->json(['message' => 'Token has been invalidated'], 200);
    }
}
