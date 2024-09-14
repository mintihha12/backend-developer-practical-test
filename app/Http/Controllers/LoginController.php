<?php

namespace App\Http\Controllers;

// use App\Http\Requests\LoginRequest;

use Illuminate\Http\Request;
use App\Http\Resources\LoginResource;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

// use App\Exceptions\AuthenticationException;

class LoginController extends Controller
{
    // public function login(LoginRequest $request)
    // {
    //     try {
    //         $user = User::where('email', $request->validated('email'))->firstOrFail();

    //         if (!Auth::attempt($request->validated())) {
    //             throw new AuthenticationException('Invalid credentials');
    //         }

    //         return LoginResource::make($user);
    //     } catch (AuthenticationException $e) {
    //         return response()->json([
    //             'status'  => Response::HTTP_UNAUTHORIZED,
    //             'message' => $e->getMessage(),
    //         ], Response::HTTP_UNAUTHORIZED);
    //     } catch (ModelNotFoundException $e) {
    //         return response()->json([
    //             'status'  => Response::HTTP_NOT_FOUND,
    //             'message' => 'Model not found.',
    //         ], Response::HTTP_NOT_FOUND);
    //     } catch (\Throwable $e) {
    //         return response()->json([
    //             'status'  => Response::HTTP_INTERNAL_SERVER_ERROR,
    //             'message' => 'Internal server error.',
    //         ], Response::HTTP_INTERNAL_SERVER_ERROR);
    //     }
    // }


    public function login(Request $request)
    {

        try {
            $validatedData = $request->validate([
                'email' => 'required|email|min:6',
                'password' => 'required|string|min:6',
            ]);


            if (!Auth::guard('ctj-api')->attempt($validatedData)) {
                throw new AuthenticationException('Invalid credentials');
            }


            $user = Auth::guard('ctj-api')->user();


            return LoginResource::make($user);
        } catch (AuthenticationException $e) {
                    return response()->json([
                        'status'  => Response::HTTP_UNAUTHORIZED,
                        'message' => $e->getMessage(),
                    ], Response::HTTP_UNAUTHORIZED);
                } catch (ModelNotFoundException $e) {
                    return response()->json([
                        'status'  => Response::HTTP_NOT_FOUND,
                        'message' => 'Model not found.',
                    ], Response::HTTP_NOT_FOUND);
                } catch (\Throwable $e) {
                    return response()->json([
                        'status'  => Response::HTTP_INTERNAL_SERVER_ERROR,
                        'message' => 'Internal server error.',
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
    }


}
