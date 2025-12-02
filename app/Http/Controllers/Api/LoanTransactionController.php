<?php

namespace App\Http\Controllers\Api;

use App\Models\LoanTransaction;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\JWT;

class LoanTransactionController extends ApiController
{
    public function delete(LoanTransaction $loanTransaction)
    {
        DB::beginTransaction();

        try {
            $loanTransaction->delete();

            DB::commit();

            return $this->success(null, "Transaksi pinjaman berhasil dihapus", 200);

        } catch (Exception $e) {

            DB::rollBack();

            return $this->error("Gagal menghapus transaksi pinjaman: " . $e->getMessage(), 500);
        }
    }
}