<?php

namespace App\Http\Controllers;

use Exception;
use App\Services\EmployeeManagement\Staff;

class StaffController extends Controller
{
    public function __construct(private readonly Staff $staff)
    {
    }

    public function payroll()
    {
        // $data = $this->staff->salary();

        // return response()->json([
        //     'data' => $data
        // ]);


        try {
            // Retrieve salary information from the Staff service
            $data = $this->staff->salary();

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (Exception $e) {
            // Handle exceptions and provide an error response
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
