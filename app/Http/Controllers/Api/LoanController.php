<?php

namespace App\Http\Controllers\Api;

use App\Models\Loan;
use App\Services\AttachmentService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\JWT;

class LoanController extends ApiController
{
    public function getLoans(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $loans = Loan::with(['borrower', 'transactions', 'files', 'images'])
            ->where('user_id', $user->id)->get();
        return $this->success($loans, "Daftar pinjaman berhasil diambil", 200);
    }

    public function create(Request $request, AttachmentService $attachmentService)
    {
        DB::beginTransaction();

        try {
            $validator = Validator::make($request->all(), [
                'borrower_id' => 'required|exists:borrowers,id',
                'amount' => 'required|integer|min:1',
                'date' => 'required|date',
                'note' => 'nullable|string|max:255',
                'attachments' => 'nullable|array',
                'attachments.*' => 'file|max:5120',
            ]);

            if ($validator->fails()) {
                return $this->error("Validation error", 422, $validator->errors());
            }

            $user = JWTAuth::parseToken()->authenticate();

            $loan = Loan::create([
                'user_id' => $user->id,
                'borrower_id' => $request->borrower_id,
                'total_amount' => $request->amount,
                'date' => $request->date,
                'description' => $request->note,
            ]);

            // ================================
            //  HANDLE ATTACHMENTS (DI SERVICE)
            // ================================
            if ($request->hasFile('attachments')) {
                $attachmentService->handleAttachments($loan, $request->file('attachments'));
            }

            DB::commit();

            return $this->success($loan->load(['images', 'files']), "Pinjaman berhasil dibuat", 201);

        } catch (\Throwable $e) {

            DB::rollBack();

            return $this->error("Gagal membuat pinjaman: " . $e->getMessage(), 500);
        }
    }

    public function detail(Loan $loan)
    {
        try {
            $loan->load([
                'borrower',
                'images',
                'files',
                'transactions' => fn($q) => $q->orderBy('date', 'desc'),
            ]);

            $paid = $loan->transactions->sum('amount');
            $remaining = $loan->total_amount - $paid;
            $loan->paid_amount = $paid;
            $loan->remaining_amount = $remaining;

            return $this->success($loan, "Detail pinjaman berhasil diambil", 200);

        } catch (Exception $e) {

            return $this->error("Gagal mengambil detail pinjaman: " . $e->getMessage(), 500);
        }
    }

    public function pay(Request $request, Loan $loan)
    {
        DB::beginTransaction();

        try {
            $validator = Validator::make($request->all(), [
                'amount' => 'required|integer|min:1',
                'note' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return $this->error("Validation error", 422, $validator->errors());
            }

            $paid = $loan->transactions()->sum('amount');
            $remaining = $loan->total_amount - $paid;

            if ($request->amount > $remaining) {
                return $this->error("Pembayaran melebihi sisa pinjaman", 422);
            }

            $loan->transactions()->create([
                'loan_id' => $loan->id,
                'amount' => $request->amount,
                'date' => now(),
                'note' => $request->note,
            ]);

            DB::commit();

            return $this->success(null, "Pembayaran berhasil dicatat", 201);

        } catch (Exception $e) {

            DB::rollBack();

            return $this->error("Gagal mencatat pembayaran: " . $e->getMessage(), 500);
        }
    }
}