<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::all();
        if (count($users) > 0) {
            return response()->json([
                'message' => count($users) . ' Users Found',
                'data' => $users
            ], 200);
        } else {
            return response()->json([
                'message' => count($users) . 'Users Found',
                'status' => '0'
            ], 200);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->messages()
            ]);
        } else {
            $data = [
                "name" => $request->name,
                "email" => $request->email,
                "password" => Hash::make($request->password)
            ];
            DB::beginTransaction();
            try {
                $user = User::create($data);
                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ]);
                $user = null;
            }

            if ($user != null) {
                return response()->json(['message' => 'success'], 200);
            } else {
                return response()->json(['message' => 'internal server error'], 500);
            }
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::find($id);
        if ($user) {
            $response = [
                'message' => 'data found',
                'status' => '1',
                'data' => $user
            ];
        } else {
            $response = [
                'message' => 'data not found',
                'status' => '0',
            ];
        }
        return response()->json($response, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::find($id);
        if (is_null($user)) {
            return response()->json(['message' => 'user not found'], 404);
        } else {
            DB::beginTransaction();
            try {
                $user->name = $request['name'];
                $user->email = $request['email'];
                $user->contact = $request['contact'];
                $user->address = $request['address'];
                $user->save();
                DB::commit();
            } catch (\Exception $e) {
                $user = null;
                DB::rollBack();
            }
            if (is_null($user)) {
                return response()->json(['message' => 'data not updated'], 400);
            } else {
                return response()->json(['message' => 'data updated'], 200);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::find($id);
        if (is_null($user)) {
            $response = [
                'message' => 'data not found',
                'status' => '0',
            ];
            $response_code = 404;
        } else {
            DB::beginTransaction();
            try {
                $user->delete();
                DB::commit();
                $response = [
                    'message' => 'data deleted',
                    'status' => '1',
                ];
                $response_code = 200;
            } catch (\Exception $e) {
                DB::rollBack();
                $response = [
                    'message' => 'data not deleted',
                    'status' => '0',
                ];
                $response_code = 400;
            }
        }
        return response()->json($response, $response_code);
    }
}
