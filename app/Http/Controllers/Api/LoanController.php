<?php

namespace App\Http\Controllers\Api;

use App\Models\Loan;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\JWT;

class LoanController extends ApiController
{
    public function getLoans(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $loans = Loan::with(['borrower'])
            ->where('user_id', $user->id)->get();
        return $this->success($loans, "Daftar pinjaman berhasil diambil", 200);
    }

    public function create(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'borrower_id' => 'required|exists:borrowers,id',
                'amount' => 'required|integer|min:1',
                'date' => 'required|date',
                'note' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return $this->error("Validation error", 422, $validator->errors());
            }

            $user = JWTAuth::parseToken()->authenticate();
            // dd($user);

            $loan = Loan::create([
                'user_id' => $user->id,
                'borrower_id' => $request->get('borrower_id'),
                'total_amount' => $request->get('amount'),
                'date' => $request->get('date'),
                'description' => $request->get('note'),
            ]);

            return $this->success($loan, "Pinjaman berhasil dibuat", 201);
        } catch (Exception $e) {
            return $this->error("Gagal membuat pinjaman: " . $e->getMessage(), 500);
        }
    }
}