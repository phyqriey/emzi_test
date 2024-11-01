<?php

namespace App\Http\Controllers;

use App\Events\EmployeeCreated;
use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'per_page' => 'integer|min:1|max:100',
        ]);
        // Default to 10 items per page, but allow customization via query parameter
        $perPage = $request->get('per_page', 10);
        $employees = Employee::orderBy('first_name')
            ->orderBy('last_name')
            ->paginate($perPage)
            ->appends(['per_page' => $perPage]);

        return response()->json($employees);
    }

    public function search(Request $request)
    {
        $request->validate([
            'per_page' => 'integer|min:1|max:100',
        ]);
        $query = $request->get('query', '');
        $perPage = $request->get('per_page', 10);

        $employees = Employee::where('first_name', 'LIKE', "%$query%")
            ->orWhere('last_name', 'LIKE', "%$query%")
            ->orWhere('position', 'LIKE', "%$query%")
            ->orWhere('department', 'LIKE', "%$query%")
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->paginate($perPage)->appends($request->all());

        return response()->json($employees);
    }

    public function handleWebhook(Request $request)
    {
        // Validate incoming webhook data
        $data = $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email|unique:employees,email',
            'phone_number' => 'nullable|string',
            'position' => 'required|string',
            'department' => 'required|string',
            'salary' => 'required|numeric',
            'hired_at' => 'required|date',
        ]);

        // Create a new employee with the validated data
        $employee = Employee::create($data);

        // Broadcast the new employee data to the frontend
        broadcast(new EmployeeCreated($employee))->toOthers();
        return response()->json(['status' => 'Employee added successfully', 'employee' => $employee], 201);
    }
}
