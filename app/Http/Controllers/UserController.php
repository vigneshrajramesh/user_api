<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;


class UserController extends Controller
{
    //
    protected $roleMaster;

    public function __construct()
    {
        $this->roleMaster = [
            'Admin' => 1,
            'Supervisor' => 2,
            'Agent' => 3
        ];
    }

    public function index()
    {
        $users = User::all();
        return response()->json([
            'status' => 'success',
            'message' => 'User Details !!',
            'data' => $users
        ],200);
    }

    public function store(UserRequest $request)
    {
        try {
            Log::info('User creation request received', ['request' => $request->all()]);
            $requestData=$request->all();
            $requestData['role'] = $this->roleMaster[$request->role];
            $requestData['password']= Hash::make(12345678);
            $userInfo=User::create($requestData);
            $token = JWTAuth::fromUser($userInfo);
            Log::info('User creation response sent', ['userId' => $userInfo->id]);
            return response()->json([
                'status' => 'success',
                'message' => 'User created successfully !!',
                'data' => [
                    'userId'=>$userInfo->id,
                    'token' =>$token
                ]
            ], 201);
        } catch (Exception $e) {
            Log::error('Exception Error', ['message' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong. Please try again later.',
                'data' => [ ]
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $credentials = $request->only('email', 'password');
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                Log::warning('Unauthorized access', ['data' => $credentials]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized',
                    'data' => []
                ], 401);
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        } catch (JWTException $e) {
            Log::error('Exception Error', ['message' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Unable to generate token. Please try again later !!',
                'data' => [ ]
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Login successfully !!',
            'data' => [
                'token'=>$token
            ]
        ], 201);
    }

    public function logout()
    {
        Auth::logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function show($id){
        try{
            $user= User::find($id);
            if(!$user){
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found !!',
                ], 404);
            }
            $userDetail=$user;
            $userDetail['role']=array_search($user->role,$this->roleMaster);
            return response()->json([
                'status' => 'success',
                'message' => 'User detail found !!',
                'data' => $userDetail
            ],200);
        } catch (Exception $e) {
            Log::error('Exception Error', ['message' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong. Please try again later.',
                'data' => [ ]
            ], 500);
        }
    }

    public function update(UserRequest $request, $id){
        try{
            Log::info('User updation request received', ['request' => $request->all()]);
            $user= User::find($id);
            if(!$user){
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found !!',
                ], 404);
            }
            $requestData=$request->all();
            $requestData['role'] = $this->roleMaster[$request->role];
            $user->update($requestData);
            Log::info('User updation response sent', ['userId' => $user->id]);
            return response()->json([
                'status' => 'success',
                'message' => 'User updated successfully !!',
                'data' => [
                    'userId'=>$user->id
                ]
            ]);
        } catch (Exception $e) {
            Log::error('Exception Error', ['message' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong. Please try again later.',
                'data' => [ ]
            ], 500);
        }
    }

    public function destroy($id)
    {
        try{
            $user = User::find($id);
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found !!',
                ], 404);
            }
            $user->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'User deleted !!',
                'data' => []
            ]);
        } catch (Exception $e) {
            Log::error('Exception Error', ['message' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong. Please try again later.',
                'data' => [ ]
            ], 500);
        }
    }

}