<?php

namespace App\Http\Controllers\Api;

use App\Models\Borrower;
use Illuminate\Http\Request;

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
}
