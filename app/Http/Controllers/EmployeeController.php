<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Employee;
use App\Models\Position;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;


class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $pageTitle = 'Employee List';

        // return view('employee.index', ['pageTitle' => $pageTitle]);

        $pageTitle = 'Employee List';

        // ELOQUENT
        $employees = Employee::all();
        return view('employee.index', [
            'pageTitle' => $pageTitle,
            'employees' => $employees
        ]);
    }



    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // $pageTitle = 'Create Employee';

        // return view('employee.create', compact('pageTitle'));

        $pageTitle = 'Create Employee';
        // RAW SQL Query
        // $positions = DB::select('select * from positions');
        //BUILD QUERY
        // $positions = DB::table('positions')->get();
        // return view('employee.create', compact('pageTitle', 'positions'));

        // ELOQUENT
        $positions = Position::all();
        return view('employee.create', compact('pageTitle', 'positions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $messages = [
            'required' => ':Attribute harus diisi.',
            'email' => 'Isi :attribute dengan format yang benar',
            'numeric' => 'Isi :attribute dengan angka'
        ];
        $validator = Validator::make($request->all(), [
            'firstName' => 'required',
            'lastName' => 'required',
            'email' => 'required|email',
            'age' => 'required|numeric',
        ], $messages);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        // Get File
        $file = $request->file('cv');
        if ($file != null) {
            $originalFilename = $file->getClientOriginalName();
            $encryptedFilename = $file->hashName();
            // Store File
            $file->store('public/files');
        }
        // ELOQUENT
        $employee = new Employee;
        $employee->firstname = $request->firstName;
        $employee->lastname = $request->lastName;
        $employee->email = $request->email;
        $employee->age = $request->age;
        $employee->position_id = $request->position;
        if ($file != null) {
            $employee->original_filename = $originalFilename;
            $employee->encrypted_filename = $encryptedFilename;
        }
        $employee->save();
        return redirect()->route('employees.index');
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $pageTitle = 'Employee Detail';

        // ELOQUENT
        $employee = Employee::find($id);
        return view('employee.show', compact('pageTitle', 'employee'));;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $pageTitle = 'Employee edit';

        // ELOQUENT
        $positions = Position::all();
        $employee = Employee::find($id);
        return view('employee.edit', compact(
            'pageTitle',
            'positions',
            'employee'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $messages = [
            'required' => ':Attribute harus diisi.',
            'email' => 'Isi :attribute dengan format yang benar',
            'numeric' => 'Isi :attribute dengan angka'
        ];
        $validator = Validator::make($request->all(), [
            'firstName' => 'required',
            'lastName' => 'required',
            'email' => 'required|email',
            'age' => 'required|numeric',
        ], $messages);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // ELOQUENT
        $employee = Employee::find($id);

        $file = $request->file('cv');
        if ($file != null) {
            $originalFilename = $file->getClientOriginalName();
            $encryptedFilename = $file->hashName();

            // Store File
            $file->store('public/files');

            $fileOld = 'public/files/' . $employee->encrypted_filename;
            if (Storage::exists($fileOld)) {
                Storage::delete($fileOld);
            }

            $employee->original_filename = $originalFilename;
            $employee->encrypted_filename = $encryptedFilename;

        }


        $employee->firstname = $request->firstName;
        $employee->lastname = $request->lastName;
        $employee->email = $request->email;
        $employee->age = $request->age;
        $employee->position_id = $request->position;



        $employee->save();
        return redirect()->route('employees.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // // QUERY BUILDER
        // DB::table('employees')
        //     ->where('id', $id)
        //     ->delete();
        // return redirect()->route('employees.index');

        // ELOQUENT
        Employee::find($id)->delete();
        return redirect()->route('employees.index');
    }

    public function downloadFile($employeeId)
    {
        $employee = Employee::find($employeeId);
        $encryptedFilename = 'public/files/' . $employee->encrypted_filename;
        $downloadFilename = Str::lower($employee->firstname . '_' . $employee->lastname . '_cv.pdf');
        if (Storage::exists($encryptedFilename)) {
            return Storage::download($encryptedFilename, $downloadFilename);
        }
    }
}
