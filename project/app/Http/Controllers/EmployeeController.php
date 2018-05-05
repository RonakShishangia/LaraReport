<?php

namespace App\Http\Controllers;

use App\Employee;
use App\Department;
use App\User;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $employees = Employee::all();
        $departments = Department::all();
        // dd($departments);
    
        return view('master.employee', compact('employees','departments'));   
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
                'name' => 'required|max:255',
                'password' => 'required|max:255|',
                'confirm_password' => 'required_with:password|same:password|max:255',
                'department_id' => 'required',
                'contact' => 'required|numeric',
                'email' => 'required|email|unique:users,email'
            ]);
        try{
            $user = new User;
            $user->name = strtoupper($request->name);
            $user->email = $request->email;
            $user->password = bcrypt($request->password);
            $user->save();

            $employee = new Employee;
            $employee->user_id = $user->id;
            $employee->name = strtoupper($request->name);
            $employee->email = $request->email;
            $employee->contact = $request->contact;
            $employee->department_id = $request->department_id;
            $employee->chat_id = $request->chat_id;
            $employee->save();
        \Session::flash("success","Success - Record Added Successfully..");
        return redirect()->route('employee.index');
            
            
        }catch(\Exception $e){
            dd($e);
            \Session::flash("error","Error - Record can not be added..");
            return redirect()->route('employee.index');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function show(Employee $employee)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function edit(Employee $employee)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Employee $employee)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function destroy(Employee $employee)
    {
        try{
            $employee->delete();
            \Session::flash("success","Success - Record Deleted Successfully..");
            return redirect()->route('employee.index');
        }catch(\Exception $e){
            \Session::flash("error","Error - Record can Not Be Deleted..");
            return redirect()->route('employee.index');

        }
    }
}
