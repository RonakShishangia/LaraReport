<?php

namespace App\Http\Controllers;

use App\AttendanceReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Excel;

class AttendanceReportController extends Controller
{
    public function importExport(){
        $data = AttendanceReport::all();
        return view('importExport', compact('data'));
	}
	public function downloadExcel($type){
		$data = Item::get()->toArray();
		return Excel::create('itsolutionstuff_example', function($excel) use ($data) {
			$excel->sheet('mySheet', function($sheet) use ($data){
				$sheet->fromArray($data);
	        });
		})->download($type);
	}

	public function importExcel(Request $request){
        if(Input::hasFile('import_file')){ 
            $path = Input::file('import_file')->getRealPath(); 
            $fileD = file($path); 
            
            $tmpFileDates = explode(',', $fileD[0]);
            
            $ckDuplicateDate = AttendanceReport::where('date', date('Y-m-d', strtotime($tmpFileDates[0])))->count();
            //dd($ckDuplicateDate);
            if($ckDuplicateDate > 0){
                session()->flash('error','Duplicate file entry.');
                return redirect('/');
            }else{
                foreach($fileD as $line){
                    $companyStartTime = "09:00:00";               // company start time
                    $companyEndTime = "19:00:00";                 // company end time
                    $search_string = ["(in)", "(out)"];           // search string
                    $replace_string = ["", ""];                   // replace searched
    
                    $tmpLine = str_getcsv($line);
                    
                    $tmpArray = explode(';', str_replace($search_string, $replace_string, rtrim($tmpLine[18], ';')));
                   
                    // $thumbs[$tmpLine[2]] = rtrim($tmpLine[18], ';');
                    $tmpLineCount = count($tmpArray);
                    $thumbs[$tmpLine[2]]['officeIn'] = reset($tmpArray);
                    $thumbs[$tmpLine[2]]['officeOut'] = end($tmpArray);
                    // count total time
                    $timDiff = strtotime(end($tmpArray)) - strtotime(reset($tmpArray));  
                    $thumbs[$tmpLine[2]]['total_time'] =  gmdate('H:i:s', $timDiff);
                   
                    $thumbs[$tmpLine[2]]['attendance'] = $tmpLine[17];
                    
                    $thumbs[$tmpLine[2]]['department'] = $tmpLine[4];
                    // thumbs ok or not
                    if($tmpLineCount % 2 != 0){
                        $thumbs[$tmpLine[2]]['not_thumb'] = false;
                    }
                    else{
                        $thumbs[$tmpLine[2]]['attendance'] = "Present";
                        $thumbs[$tmpLine[2]]['not_thumb'] = true;
                    }
                   
                    $thumbs[$tmpLine[2]]['break'] = explode(';', str_replace($search_string, $replace_string, rtrim($tmpLine[18], ';')));
                    
                    array_shift($thumbs[$tmpLine[2]]['break']);
                    array_pop($thumbs[$tmpLine[2]]['break']);
    
                    $thumbs[$tmpLine[2]]['break'] = array_chunk($thumbs[$tmpLine[2]]['break'], 2);
    
                    $sum = strtotime('00:00:00');
                    $sum2=0; 
                    foreach ($thumbs[$tmpLine[2]]['break'] as &$break) {
                        $countElements = count($break);
                        if ($countElements % 2 == 0) {
                            $breakTime = strtotime($break[1]) - strtotime($break[0]); 
                            array_push($break, gmdate('H:i:s', $breakTime));
                            $breaksTimes[] = gmdate('H:i:s', $breakTime);
                            $sum1=strtotime(gmdate('H:i:s', $breakTime))-$sum;
                            $sum2 = $sum2+$sum1;
                        }
                    }
                    // sum of all breaks
                    $sum3=$sum+$sum2;
                    // count total break time
                    $thumbs[$tmpLine[2]]['total_break_time'] = date("H:i:s",$sum3);
                    // total worked time 
                    $working_time = $timDiff-$sum3;
                    $thumbs[$tmpLine[2]]['worked_time'] = gmdate("H:i:s", $working_time);
                    
                    $officeInSE = str_contains($tmpLine[10] , "(SE)");
                    $officeOutSE = str_contains($tmpLine[11] , "(SE)");
                    // dd($tmpLine[11]);
                    if($officeInSE || $officeOutSE){
                        $thumbs[$tmpLine[2]]['note'] = "System error thumb <br><b>". ($officeInSE == true ? $tmpLine[10] : "").($officeOutSE == true ? $tmpLine[11] : "")."</b>";
                    }else{
                        $thumbs[$tmpLine[2]]['note'] = null;
                    }
                    
                    $entryDiff = strtotime($companyStartTime) - strtotime($thumbs[$tmpLine[2]]['officeIn']);
                    $exitDiff =  strtotime($companyEndTime) - strtotime($thumbs[$tmpLine[2]]['officeOut']);
                    
                    // check "not_thumb is false of not
                    if($thumbs[$tmpLine[2]]['not_thumb'] == true){
                        // Late entry time diffrence
                        if($thumbs[$tmpLine[2]]['attendance'] == "Present"){
                            if(strtotime($thumbs[$tmpLine[2]]['officeIn']) > strtotime($companyStartTime))
                            $entryTimeDiff = gmdate('H:i:s', abs($entryDiff));
                            elseif(strtotime($thumbs[$tmpLine[2]]['officeIn']) < strtotime($companyStartTime))
                            $entryTimeDiff = '-'.gmdate('H:i:s', abs($entryDiff));
                            else
                            $entryTimeDiff = "00:00:00";
                        }
                        // (OT) Exit time diffrence
                        if($thumbs[$tmpLine[2]]['attendance'] == "Present"){
                            if(strtotime($thumbs[$tmpLine[2]]['officeOut']) > strtotime($companyEndTime))
                            $exitTimeDiff = gmdate('H:i:s', abs($exitDiff));
                            elseif(strtotime($thumbs[$tmpLine[2]]['officeOut']) < strtotime($companyEndTime))
                            $exitTimeDiff = '-'.gmdate('H:i:s', abs($exitDiff));
                            else
                            $exitTimeDiff = "00:00:00";
                        }
                    }else{
                        $entryTimeDiff = "00:00:00";
                        $exitTimeDiff = "00:00:00";
                    }
                    // dd($entryTimeDiff);                
                    // Exit time diffrence
                    // $thumbs[$tmpLine[2]]['attendance'] == "Present" ? $exitTimeDiff = gmdate('H:i:s', $exitDiff) : $exitTimeDiff = "00:00:00";
    
                    // dd($thumbs[$tmpLine[2]]['attendance']);
                    $tmpLine[0] = date('Y-m-d', strtotime($tmpLine[0]));
                    $tmpLine[10] = str_replace('(SE)', '', $tmpLine[10]);
                    $tmpLine[11] = str_replace('(SE)', '', $tmpLine[11]);
                    $tmpLine[14] = str_replace('0-', '00:', $tmpLine[14]);
                    // echo "<pre>";
                    // print_r($thumbs[$tmpLine[2]]['break']);
                    // dd();
                    
                    // add data to database
                    $attendanceReportDatas = new AttendanceReport();
                    $attendanceReportDatas->date = $tmpLine[0];
                    $attendanceReportDatas->name = $tmpLine[2];                
                    $attendanceReportDatas->department = $tmpLine[4];                
                    $attendanceReportDatas->officeIn = $thumbs[$tmpLine[2]]['officeIn'];                
                    $attendanceReportDatas->officeOut = $thumbs[$tmpLine[2]]['officeOut'];
                    // store total time
                    $timDiff = strtotime($thumbs[$tmpLine[2]]['officeOut']) - strtotime($thumbs[$tmpLine[2]]['officeIn']);    
                    $attendanceReportDatas->total_time = $thumbs[$tmpLine[2]]['total_time'];
                    // store total break time
                    $attendanceReportDatas->total_break_time = $thumbs[$tmpLine[2]]['total_break_time'];
                    // store total worked time
                    $attendanceReportDatas->worked_time = $thumbs[$tmpLine[2]]['worked_time'];
                    $attendanceReportDatas->LE = $entryTimeDiff;        
                    $attendanceReportDatas->OT = $exitTimeDiff; //gmdate('H:i:s', $tmpLine[13]*60);        
                    $attendanceReportDatas->attendance = $thumbs[$tmpLine[2]]['attendance'];                
                    $attendanceReportDatas->thumbs = $tmpLine[18];          
                    $attendanceReportDatas->breaks = json_encode($thumbs[$tmpLine[2]]['break']);          
                    $attendanceReportDatas->note = $thumbs[$tmpLine[2]]['note'];          
                    $attendanceReportDatas->not_thumb = $thumbs[$tmpLine[2]]['not_thumb'];
                    $attendanceReportDatas->save();
                }
                //return response()->json($thumbs);
                return redirect('/');
                // return view('importExport', compact('data'));
            }
        }
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = AttendanceReport::all()->toJson();
        return view('importExport', $data);
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\AttendanceReport  $attendanceReport
     * @return \Illuminate\Http\Response
     */
    public function show(AttendanceReport $attendanceReport)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\AttendanceReport  $attendanceReport
     * @return \Illuminate\Http\Response
     */
    public function edit(AttendanceReport $attendanceReport)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\AttendanceReport  $attendanceReport
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AttendanceReport $attendanceReport)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\AttendanceReport  $attendanceReport
     * @return \Illuminate\Http\Response
     */
    public function destroy(AttendanceReport $attendanceReport)
    {
        //
    }

    /**
     * Get all data of attendance
     * @return \Illuminate\Http\Response
     */
    public function getAttendanceReport(){
        $data = AttendanceReport::all()->toJson();
        return view('importExport', compact('data'));
        // return response()->json([
        //     'response' => true,
        //     'data' => $data
        // ]);
    }
}
