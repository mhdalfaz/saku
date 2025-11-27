<?php

namespace App\Http\Controllers\Api;

use App\Models\Borrower;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BorrowerController extends ApiController
{
    public function getBorrowers(Request $request)
    {
        try {
            $borrowers = Borrower::where('name', 'like', '%' . $request->query('search') . '%')->get();
            return $this->success($borrowers, "Data borrowers");
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:borrowers',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->error("Validation error", 422, $validator->errors());
        }

        try {
            $borrower = Borrower::create([
                'name' => $request->get('name'),
                'email' => $request->get('email'),
                'phone' => $request->get('phone'),
                'address' => $request->get('address'),
            ]);

            return $this->success($borrower, "Borrower created successfully", 201);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}
