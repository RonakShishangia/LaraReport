<?php

namespace App\Http\Controllers;

use App\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $departments = Department::all();
        // dd($departments);
        return view('master.department', compact('departments'));
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
            'name' => 'required|max:255|unique:departments,name,'. $request->editId,
        ]);
        try{
            if($request->editId==""){
                $department = new Department;
                $department->name = strtoupper($request->name);
                $department->save();
                \Session::flash("success","Success - Record Added Successfully..");
                return redirect()->route('department.index');
            }else{
                $department = Department::find($request->editId);
                $department->name = strtoupper($request->name);
                $department->save();
                \Session::flash("success","Success - Record Updated Successfully..");
                return redirect()->route('department.index');
            }
        }catch(\Exception $e){
            \Session::flash("error","Error - Record can not be added..");
            return redirect()->route('department.index');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Department  $department
     * @return \Illuminate\Http\Response
     */
    public function show(Department $department)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Department  $department
     * @return \Illuminate\Http\Response
     */
    public function edit(Department $department)
    {
        try{
            
        }catch(\Exception $e){

        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Department  $department
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Department $department)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Department  $department
     * @return \Illuminate\Http\Response
     */
    public function destroy(Department $department)
    {
        try{
            $department->delete();
            \Session::flash("success","Success - Record Deleted Successfully..");
            return redirect()->route('department.index');
        }catch(\Exception $e){
            dd($e);
            \Session::flash("error","Error - Record can Not Be Deleted..");
            return redirect()->route('department.index');
        }

    }
}
