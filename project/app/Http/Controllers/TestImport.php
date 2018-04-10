<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Input;
use Excel;

class TestImport extends Controller
{
    public function importExcel(Request $request){
		try{
            // dd($request->file('file'));
				$path = $request->file('file')->getRealPath();
				$data = Excel::load($path, function($reader){
                    dd($reader->toArray());
                    foreach ($reader->toArray()[1] as $key => $value) {
                        dd($value);
                    }
                });
                
            // dd($request);
			\Session::flash("notification","Success - Record Updated Successfully..");
			return redirect()->route('design.index');
		}catch(\Exception $ex){
			dd($ex);
			\Session::flash("notification","Error in Record Delete...");
			return redirect()->back();
		}
		
	}
}
