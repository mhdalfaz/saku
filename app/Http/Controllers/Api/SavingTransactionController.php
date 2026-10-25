<?php

namespace App\Http\Controllers\Api;

use App\Models\SavingTransaction;
use Exception;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class SavingTransactionController extends ApiController
{
    public function delete(SavingTransaction $savingTransaction)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            $saving = $savingTransaction->parentSaving;

            if (! $saving || (int) $saving->user_id !== (int) $user->id) {
                return $this->error('Data tidak ditemukan', 404);
            }

            $balanceDelta = match ($savingTransaction->type) {
                'withdrawal' => (int) $savingTransaction->amount,
                'profit' => $savingTransaction->retained ? -(int) $savingTransaction->amount : 0,
                default => -(int) $savingTransaction->amount,
            };

            if ($saving->balance + $balanceDelta < 0) {
                return $this->error('Transaksi tidak bisa dihapus karena membuat saldo menjadi negatif', 422);
            }

            DB::beginTransaction();

            $savingTransaction->delete();

            DB::commit();

            return $this->success(null, 'Transaksi simpanan berhasil dihapus', 200);
        } catch (Exception $e) {
            DB::rollBack();

            return $this->error('Gagal menghapus transaksi simpanan: '.$e->getMessage(), 500);
        }
    }
}
