<?php

namespace App\Http\Controllers;

use App\AttendanceReport;
use App\Company;
use App\Department;
use App\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Excel;
use App\Mail\DailyReportMail;

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
        try{
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
                        $search_string = ["(in)", "(out)"];           // search string
                        $replace_string = ["", ""];                   // replace searched
                        
                        $tmpLine = str_getcsv($line);
                        
                        if($tmpLine[17] == "On WeeklyOff"){
                            $tmpArray = array();
                        }else{
                            $tmpArray = explode(';', str_replace($search_string, $replace_string, rtrim($tmpLine[18], ';')));
                        }
                        // dd($tmpLine[17]);
                        
                        $employee=Employee::where('name','=',strtoupper($tmpLine[2]))->first();
                        if(!($employee))
                          continue;
                        $companyStartTime = $employee->company->startTime;
                        $companyEndTime = $employee->company->endTime;
                        $companyDutyTime = $employee->company->dutyTime;
    
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
                            $thumbs[$tmpLine[2]]['not_thumb'] = true;
                            if($tmpLine[17] == "On WeeklyOff"){
                                $thumbs[$tmpLine[2]]['attendance'] = "On WeeklyOff";
                            }elseif($thumbs[$tmpLine[2]]['attendance'] == "Present On WeeklyOff" || $thumbs[$tmpLine[2]]['attendance'] == "Present"){
                                $thumbs[$tmpLine[2]]['attendance'] == "Present";
                            }
                        }
                        
                        if($tmpLine[17] == "On WeeklyOff"){
                            $thumbs[$tmpLine[2]]['break'] = array();
                        }else{
                            $thumbs[$tmpLine[2]]['break'] = explode(';', str_replace($search_string, $replace_string, rtrim($tmpLine[18], ';')));
                        }
    
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
                        $ot=strtotime($companyDutyTime) - strtotime($thumbs[$tmpLine[2]]['worked_time']);
    
                        // check "not_thumb is false of not
                        if($thumbs[$tmpLine[2]]['attendance'] == "Present" || $thumbs[$tmpLine[2]]['attendance'] == "Present On WeeklyOff" ){
                            // Late entry time diffrence
                            // if($thumbs[$tmpLine[2]]['attendance'] == "Present"){
                                if(strtotime($thumbs[$tmpLine[2]]['officeIn']) > strtotime($companyStartTime))
                                    $entryTimeDiff = gmdate('H:i:s', abs($entryDiff));
                                elseif(strtotime($thumbs[$tmpLine[2]]['officeIn']) < strtotime($companyStartTime))
                                    $entryTimeDiff = '-'.gmdate('H:i:s', abs($entryDiff));
                                else
                                    $entryTimeDiff = "00:00:00";
                            // }
                            // (OT) Exit time diffrence
                            // if($thumbs[$tmpLine[2]]['attendance'] == "Present"){
                                if(strtotime($thumbs[$tmpLine[2]]['worked_time']) > strtotime($companyDutyTime))
                                    $overTime = gmdate('H:i:s', abs($ot));
                                elseif(strtotime($thumbs[$tmpLine[2]]['worked_time']) < strtotime($companyDutyTime))
                                    $overTime = '-'.gmdate('H:i:s', $ot);
                                else
                                    $overTime = "00:00:00";
                            // }
                        }else{
                            $entryTimeDiff = "00:00:00";
                            $overTime = "00:00:00";
                        }
    
                        // dd($thumbs[$tmpLine[2]]['attendance']);
                        $tmpLine[0] = date('Y-m-d', strtotime($tmpLine[0]));
                        $tmpLine[10] = str_replace('(SE)', '', $tmpLine[10]);
                        $tmpLine[11] = str_replace('(SE)', '', $tmpLine[11]);
                        $tmpLine[14] = str_replace('0-', '00:', $tmpLine[14]);
                        // add data to database
                        $attendanceReportDatas = new AttendanceReport();
                        $attendanceReportDatas->employee_id = $employee->id;
                        $attendanceReportDatas->date = $tmpLine[0];
                        $attendanceReportDatas->name = $tmpLine[2];
                        $attendanceReportDatas->department = $employee->department->name;//$tmpLine[4];
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
                        $attendanceReportDatas->OT = $overTime; //gmdate('H:i:s', $tmpLine[13]*60);
                        // dd($attendanceReportDatas);
                        $attendanceReportDatas->attendance = $thumbs[$tmpLine[2]]['attendance'];
                        $attendanceReportDatas->thumbs = ($tmpLine[17] == "On WeeklyOff") ? "" : $tmpLine[18] ;
                        $attendanceReportDatas->breaks = json_encode($thumbs[$tmpLine[2]]['break']);
                        $attendanceReportDatas->note = $thumbs[$tmpLine[2]]['note'];
                        $attendanceReportDatas->not_thumb = $thumbs[$tmpLine[2]]['not_thumb'];
                        $attendanceReportDatas->save();
                        // send email 
                        // \Mail::to($employee->email)->send(new DailyReportMail($attendanceReportDatas));
                    }
<<<<<<< HEAD
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
                    $ot=strtotime($companyDutyTime) - strtotime($thumbs[$tmpLine[2]]['worked_time']);

                    // check "not_thumb is false of not
                    if($thumbs[$tmpLine[2]]['attendance'] == "Present" || $thumbs[$tmpLine[2]]['attendance'] == "Present On WeeklyOff" ){
                        // Late entry time diffrence
                        // if($thumbs[$tmpLine[2]]['attendance'] == "Present"){
                            if(strtotime($thumbs[$tmpLine[2]]['officeIn']) > strtotime($companyStartTime))
                                $entryTimeDiff = gmdate('H:i:s', abs($entryDiff));
                            elseif(strtotime($thumbs[$tmpLine[2]]['officeIn']) < strtotime($companyStartTime))
                                $entryTimeDiff = '-'.gmdate('H:i:s', abs($entryDiff));
                            else
                                $entryTimeDiff = "00:00:00";
                        // }
                        // (OT) Exit time diffrence
                        // if($thumbs[$tmpLine[2]]['attendance'] == "Present"){
                            if(strtotime($thumbs[$tmpLine[2]]['worked_time']) > strtotime($companyDutyTime))
                                $overTime = gmdate('H:i:s', abs($ot));
                            elseif(strtotime($thumbs[$tmpLine[2]]['worked_time']) < strtotime($companyDutyTime))
                                $overTime = '-'.gmdate('H:i:s', $ot);
                            else
                                $overTime = "00:00:00";
                        // }
                    }else{
                        $entryTimeDiff = "00:00:00";
                        $overTime = "00:00:00";
                    }

                    // dd($thumbs[$tmpLine[2]]['attendance']);
                    $tmpLine[0] = date('Y-m-d', strtotime($tmpLine[0]));
                    $tmpLine[10] = str_replace('(SE)', '', $tmpLine[10]);
                    $tmpLine[11] = str_replace('(SE)', '', $tmpLine[11]);
                    $tmpLine[14] = str_replace('0-', '00:', $tmpLine[14]);
                    // add data to database
                    $attendanceReportDatas = new AttendanceReport();
                    $attendanceReportDatas->employee_id = $employee->id;
                    $attendanceReportDatas->date = $tmpLine[0];
                    $attendanceReportDatas->name = $tmpLine[2];
                    $attendanceReportDatas->department = $employee->department->name;//$tmpLine[4];
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
                    $attendanceReportDatas->OT = $overTime; //gmdate('H:i:s', $tmpLine[13]*60);
                    // dd($attendanceReportDatas);
                    $attendanceReportDatas->attendance = $thumbs[$tmpLine[2]]['attendance'];
                    $attendanceReportDatas->thumbs = $tmpLine[18];
                    $attendanceReportDatas->breaks = json_encode($thumbs[$tmpLine[2]]['break']);
                    $attendanceReportDatas->note = $thumbs[$tmpLine[2]]['note'];
                    $attendanceReportDatas->not_thumb = $thumbs[$tmpLine[2]]['not_thumb'];
                    // return view('emails.dailyReportMail',compact('attendanceReportDatas'));
                    $attendanceReportDatas->save();
                    // send email
                    // \Mail::to($employee->email)->send(new DailyReportMail($attendanceReportDatas));
=======
                    return redirect('/');
>>>>>>> a42ebea230f7379282f5ee6824c5f96fbffcdc16
                }
            }
        }catch(\Exception $ex){
            dd($ex);
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
    }

    public function searchemp(){
        $employees = Employee::get();
        $empDatas = [];
        return view('report', compact('employees', 'empDatas'));
    }

    // get selected employee
    public function getEmpReport(Request $request) {
        try{
            $seconds = 0;
            $startDate = date("Y-m-d",strtotime($request->startDate));
            $endDate = date("Y-m-d",strtotime($request->endDate));
            $employee = $request->employee;

            $employees = Employee::get();
            $empDatas = AttendanceReport::where('employee_id', $employee)
                                        // ->where('LE', 'NOT LIKE',  "-%")
                                        ->whereBetween('date', [$startDate, $endDate])
                                        ->orderBy('date', 'asc')->get();
            foreach($empDatas as $empData){
                $tempCalcData = [];
                list( $g, $i, $s ) = explode(":",$empData->LE);
                $seconds += $g * 3600;
                $seconds += $i * 60;
                $seconds += $s;

                $empData['day'] = date('l', strtotime($empData->date));
            }
            $hours    = floor( $seconds / 3600 );
            $seconds -= $hours * 3600;
            $minutes  = floor( $seconds / 60 );
            $seconds -= $minutes * 60;
         
            $totalLETime = $hours.":".$minutes.":".$seconds;
            $tmpView = view('empreport', compact('employees', 'empDatas', 'totalLETime', 'startDate', 'endDate', 'employee'))->render();
            return response()->json([
                'tmp' => $tmpView,
            ]);
            return view('report', compact('employees', 'empDatas', 'totalLETime', 'startDate', 'endDate', 'employee'));
        }catch(\Exception $ex){
            dd($ex);
            session()->flash('error','Error :  Something went wrong.');
            return redirect('/');
        }

    }

    /**
     * add note to into the  employee
     */
    public function addnote(Request $request){
        try{
            $data = AttendanceReport::find($request->userId);
            $data->note = $request->adduserNote;
            $data->save();
            session()->flash('success','NOTE added successfully');
            return redirect('/');
        }catch(\Exception $ex){
            session()->flash('error','Error while entering NOTE');
            return redirect('/');
        }

    }
}
