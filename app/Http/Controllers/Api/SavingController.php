<?php

namespace App\Http\Controllers\Api;

use App\Models\Custodian;
use App\Models\Saving;
use App\Services\AttachmentService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class SavingController extends ApiController
{
    public function getSavings(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            $query = Saving::with(['custodian', 'transactions', 'files', 'images'])
                ->where('user_id', $user->id);

            if (in_array($request->status, ['ongoing', 'closed'], true)) {
                $query->where('status', $request->status);
            }

            $savings = $query->get();

            return $this->success($savings, 'Daftar simpanan berhasil diambil', 200);
        } catch (Exception $e) {
            return $this->error('Gagal mengambil data: '.$e->getMessage(), 500);
        }
    }

    public function create(Request $request, AttachmentService $attachmentService)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            $validator = Validator::make($request->all(), [
                'custodian_id' => 'required|exists:custodians,id',
                'amount' => 'required|integer|min:1',
                'date' => 'required|date',
                'note' => 'nullable|string|max:255',
                'attachments' => 'nullable|array',
                'attachments.*' => 'file|max:5120',
            ]);

            if ($validator->fails()) {
                return $this->error('Validation error', 422, $validator->errors());
            }

            $custodianExists = Custodian::where('id', $request->custodian_id)
                ->where('user_id', $user->id)
                ->exists();

            if (! $custodianExists) {
                return $this->error('Penampung tidak ditemukan', 422);
            }

            DB::beginTransaction();

            $saving = Saving::create([
                'user_id' => $user->id,
                'custodian_id' => $request->custodian_id,
                'date' => $request->date,
                'description' => $request->note,
                'status' => 'ongoing',
            ]);

            $saving->transactions()->create([
                'type' => 'deposit',
                'amount' => $request->amount,
                'note' => 'Setoran awal',
                'date' => $request->date,
            ]);

            if ($request->hasFile('attachments')) {
                $attachmentService->handleAttachments($saving, $request->file('attachments'), 'uploads/savings');
            }

            DB::commit();

            return $this->success($saving->load(['custodian', 'images', 'files']), 'Simpanan berhasil dibuat', 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            return $this->error('Gagal membuat simpanan: '.$e->getMessage(), 500);
        }
    }

    public function detail(Saving $saving)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if ((int) $saving->user_id !== (int) $user->id) {
                return $this->error('Data tidak ditemukan', 404);
            }

            $saving->load([
                'custodian',
                'images',
                'files',
                'transactions' => fn ($q) => $q->orderBy('date', 'desc')->orderBy('id', 'desc'),
            ]);

            return $this->success($saving, 'Detail simpanan berhasil diambil', 200);
        } catch (Exception $e) {
            return $this->error('Gagal mengambil detail simpanan: '.$e->getMessage(), 500);
        }
    }

    public function deposit(Request $request, Saving $saving)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if ((int) $saving->user_id !== (int) $user->id) {
                return $this->error('Data tidak ditemukan', 404);
            }

            $validator = Validator::make($request->all(), [
                'amount' => 'required|integer|min:1',
                'note' => 'nullable|string|max:255',
                'date' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                return $this->error('Validation error', 422, $validator->errors());
            }

            if ($saving->status !== 'ongoing') {
                return $this->error('Simpanan sudah ditutup, tidak bisa menambah setoran', 422);
            }

            DB::beginTransaction();

            $saving->transactions()->create([
                'type' => 'deposit',
                'amount' => $request->amount,
                'note' => $request->note,
                'date' => $request->date ?? now()->toDateString(),
            ]);

            DB::commit();

            return $this->success(null, 'Setoran berhasil dicatat', 201);
        } catch (Exception $e) {
            DB::rollBack();

            return $this->error('Gagal mencatat setoran: '.$e->getMessage(), 500);
        }
    }

    public function withdraw(Request $request, Saving $saving)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if ((int) $saving->user_id !== (int) $user->id) {
                return $this->error('Data tidak ditemukan', 404);
            }

            $validator = Validator::make($request->all(), [
                'amount' => 'required|integer|min:1',
                'note' => 'nullable|string|max:255',
                'date' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                return $this->error('Validation error', 422, $validator->errors());
            }

            if ($saving->status !== 'ongoing') {
                return $this->error('Simpanan sudah ditutup, tidak bisa melakukan penarikan', 422);
            }

            DB::beginTransaction();

            if ($request->amount > $saving->balance) {
                DB::rollBack();

                return $this->error('Penarikan melebihi saldo tertahan', 422);
            }

            $saving->transactions()->create([
                'type' => 'withdrawal',
                'amount' => $request->amount,
                'note' => $request->note,
                'date' => $request->date ?? now()->toDateString(),
            ]);

            DB::commit();

            return $this->success(null, 'Penarikan berhasil dicatat', 201);
        } catch (Exception $e) {
            DB::rollBack();

            return $this->error('Gagal mencatat penarikan: '.$e->getMessage(), 500);
        }
    }

    public function profit(Request $request, Saving $saving)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if ((int) $saving->user_id !== (int) $user->id) {
                return $this->error('Data tidak ditemukan', 404);
            }

            $validator = Validator::make($request->all(), [
                'amount' => 'required|integer|min:1',
                'note' => 'nullable|string|max:255',
                'date' => 'nullable|date',
                'retained' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return $this->error('Validation error', 422, $validator->errors());
            }

            if ($saving->status !== 'ongoing') {
                return $this->error('Simpanan sudah ditutup, tidak bisa mencatat keuntungan', 422);
            }

            DB::beginTransaction();

            $saving->transactions()->create([
                'type' => 'profit',
                'retained' => $request->boolean('retained'),
                'amount' => $request->amount,
                'note' => $request->note,
                'date' => $request->date ?? now()->toDateString(),
            ]);

            DB::commit();

            return $this->success(null, 'Keuntungan berhasil dicatat', 201);
        } catch (Exception $e) {
            DB::rollBack();

            return $this->error('Gagal mencatat keuntungan: '.$e->getMessage(), 500);
        }
    }

    public function close(Saving $saving)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if ((int) $saving->user_id !== (int) $user->id) {
                return $this->error('Data tidak ditemukan', 404);
            }

            if ($saving->status !== 'ongoing') {
                return $this->error('Simpanan sudah ditutup', 422);
            }

            $saving->status = 'closed';
            $saving->save();

            return $this->success(null, 'Simpanan berhasil ditutup', 200);
        } catch (Exception $e) {
            return $this->error('Gagal menutup simpanan: '.$e->getMessage(), 500);
        }
    }

    public function delete(Saving $saving)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if ((int) $saving->user_id !== (int) $user->id) {
                return $this->error('Data tidak ditemukan', 404);
            }

            DB::beginTransaction();

            $saving->transactions()->delete();
            $saving->delete();

            DB::commit();

            return $this->success(null, 'Simpanan berhasil dihapus', 200);
        } catch (Exception $e) {
            DB::rollBack();

            return $this->error('Gagal menghapus simpanan: '.$e->getMessage(), 500);
        }
    }

    public function getSavingsByCustodian(Request $request, $custodian)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            $query = Saving::with(['custodian', 'transactions'])
                ->where('user_id', $user->id)
                ->where('custodian_id', $custodian)
                ->orderBy('date', 'asc');

            if (in_array($request->status, ['ongoing', 'closed'], true)) {
                $query->where('status', $request->status);
            }

            $savings = $query->get();

            return $this->success($savings, 'Daftar simpanan penampung berhasil diambil', 200);
        } catch (Exception $e) {
            return $this->error('Gagal mengambil data: '.$e->getMessage(), 500);
        }
    }
}
