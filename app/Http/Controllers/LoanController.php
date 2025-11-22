<?php 

namespace App\Http\Controllers;

use App\Models\Borrower;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class LoanController extends Controller
{
    public function createPage()
    {
        $borrowers = Borrower::all();
        return view('pages.loans.create', compact('borrowers'));
    }
}