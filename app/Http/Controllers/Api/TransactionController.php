<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    public function pay(Request $request)
    {
        $request->validate([
            'rfid_uid' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $rfid = $request->rfid_uid;
        $amount = $request->amount;

        try {
            $result = DB::transaction(function () use ($rfid, $amount) {
                // 1. Find User by RFID
                $user = User::where('rfid_uid', $rfid)->first();

                if (!$user) {
                    throw new \Exception('Card not registered', 404);
                }

                if (!$user->wallet) {
                    throw new \Exception('Wallet not found', 404);
                }

                // 2. Lock Wallet Row for Update (Prevent Race Condition)
                $wallet = DB::table('wallets')->where('id', $user->wallet->id)->lockForUpdate()->first();

                // 3. Check Balance
                if ($wallet->balance < $amount) {
                    throw new \Exception('Insufficient balance', 400);
                }

                // 4. Deduct Balance
                $newBalance = $wallet->balance - $amount;
                DB::table('wallets')->where('id', $wallet->id)->update(['balance' => $newBalance]);

                // 5. Create Transaction Record
                $trx = Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'payment',
                    'amount' => $amount,
                    'reference_id' => 'TRX-' . time() . '-' . Str::random(5),
                    'status' => 'success'
                ]);

                return [
                    'transaction' => $trx,
                    'new_balance' => $newBalance,
                    'user_name' => $user->name,
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            $code = $e->getCode();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], ($code >= 400 && $code < 600) ? $code : 500);
        }
    }
}
