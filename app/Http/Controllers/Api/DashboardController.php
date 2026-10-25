<?php

namespace App\Http\Controllers\Api;

use App\Models\Loan;
use App\Models\LoanTransaction;
use App\Models\SavingTransaction;
use Exception;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class DashboardController extends ApiController
{
    public function summary()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            // Cash flow: pemasukan - pengeluaran (saat ini tabel masih kosong = 0)
            $cashIn = (int) DB::table('cash_flows')
                ->where('user_id', $user->id)
                ->where('type', 'income')
                ->whereNull('deleted_at')
                ->sum('amount');

            $cashOut = (int) DB::table('cash_flows')
                ->where('user_id', $user->id)
                ->where('type', 'expense')
                ->whereNull('deleted_at')
                ->sum('amount');

            // Piutang
            $totalLent = (int) Loan::where('user_id', $user->id)->sum('total_amount');
            $totalPaid = (int) LoanTransaction::query()
                ->whereHas('loan', fn ($q) => $q->where('user_id', $user->id))
                ->sum('amount');

            // Simpanan: saldo = setoran + profit ditahan - penarikan
            $savingAgg = SavingTransaction::query()
                ->whereHas('parentSaving', fn ($q) => $q->where('user_id', $user->id))
                ->selectRaw("
                    COALESCE(SUM(CASE WHEN type = 'deposit' THEN amount ELSE 0 END), 0) as total_deposit,
                    COALESCE(SUM(CASE WHEN type = 'withdrawal' THEN amount ELSE 0 END), 0) as total_withdrawal,
                    COALESCE(SUM(CASE WHEN type = 'profit' AND retained = true THEN amount ELSE 0 END), 0) as profit_retained
                ")
                ->first();

            $savingsBalance = (int) ($savingAgg->total_deposit ?? 0)
                + (int) ($savingAgg->profit_retained ?? 0)
                - (int) ($savingAgg->total_withdrawal ?? 0);

            return $this->success([
                'cash_flow_net' => $cashIn - $cashOut,
                'total_lent' => $totalLent,
                'loan_outstanding' => $totalLent - $totalPaid,
                'savings_balance' => $savingsBalance,
            ], 'Ringkasan berhasil diambil', 200);
        } catch (Exception $e) {
            return $this->error('Gagal mengambil ringkasan: '.$e->getMessage(), 500);
        }
    }
}
