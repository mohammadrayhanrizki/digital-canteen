<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class RfidController extends Controller
{
    public function check(Request $request)
    {
        $request->validate([
            'rfid_uid' => 'required|string'
        ]);

        $user = User::where('rfid_uid', $request->rfid_uid)
                    ->with('wallet')
                    ->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Card not registered',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'name' => $user->name,
                'role' => $user->role,
                'balance' => $user->wallet ? $user->wallet->balance : 0,
                'rfid_uid' => $user->rfid_uid
            ]
        ]);
    }
}
