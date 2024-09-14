<?php

namespace App\Http\Controllers;

use App\Services\EmployeeManagement\Applicant;
use Illuminate\Http\Request;

class JobController extends Controller
{
    public function __construct(private readonly Applicant $applicant)
    {
    }

    public function apply(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'resume' => 'required|file|mimes:pdf,doc,docx|max:2048',
        ]);

        try {
            $data = $this->applicant->applyJob($validatedData);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);

        }
    }
}
