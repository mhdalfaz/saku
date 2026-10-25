<?php

namespace App\Http\Controllers\Api;

use App\Models\Custodian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Tymon\JWTAuth\Facades\JWTAuth;

class CustodianController extends ApiController
{
    public function getCustodians(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            $custodians = Custodian::where('user_id', $user->id)
                ->where('name', 'like', '%'.$request->query('search').'%')
                ->get();

            return $this->success($custodians, 'Data penampung berhasil diambil');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function create(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => [
                    'nullable', 'string', 'email', 'max:255',
                    Rule::unique('custodians')->where('user_id', $user->id),
                ],
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return $this->error('Validation error', 422, $validator->errors());
            }

            $custodian = Custodian::create([
                'user_id' => $user->id,
                'name' => $request->get('name'),
                'email' => $request->get('email'),
                'phone' => $request->get('phone'),
                'address' => $request->get('address'),
            ]);

            return $this->success($custodian, 'Penampung berhasil dibuat', 201);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}
