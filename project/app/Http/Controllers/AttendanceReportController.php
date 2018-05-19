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
use App\Mail\MonthlyReportMail;

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

				$previousDate = strtotime('-1 day', strtotime($tmpFileDates[0]));
				// check previous date file uploaded or not
				$ckPreviousDate = AttendanceReport::where('date', date('Y-m-d', $previousDate))->count();
				if($ckPreviousDate < 1){
					session()->flash('error','You have to upload '.date('d-m-Y', $previousDate).'`s file first');
					return redirect('/');
				}

				// check duplicate entry exist or not
				$ckDuplicateDate = AttendanceReport::where('date', date('Y-m-d', strtotime($tmpFileDates[0])))->count();
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
						$employee=Employee::where('name','=',strtoupper($tmpLine[2]))->first();
						if(!($employee))
						  continue;
						$companyStartTime = $employee->company->startTime;
						$companyEndTime = $employee->company->endTime;
						$companyDutyTime = $employee->company->dutyTime;

						// $thumbs[$tmpLine[2]] = rtrim($tmpLine[18], ';');
						$tmpLineCount = count($tmpArray);
						$thumbs[$tmpLine[2]]['officeIn'] = reset($tmpArray)=="" ? "00:00:00" : reset($tmpArray);
						$thumbs[$tmpLine[2]]['officeOut'] = end($tmpArray)=="" ? "00:00:00" :  end($tmpArray);
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
						$firstDate=explode("-",$tmpLine[0]);
						if($firstDate[2]=="01")
							$this->lastMonthData($employee->id);
					}
				}
				session()->flash('success','File successfully added.');
				return redirect()->back();
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
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function lastMonthData($employee_id){
		$start = new \Carbon\Carbon('first day of last month');
		$end = new \Carbon\Carbon('last day of last month');
		try{
			$seconds = 0;
			$startDate =$start->toDateString();
			$endDate = $end->toDateString();
			$empDatas = AttendanceReport::where('employee_id', $employee_id)
			->whereBetween('date', [$startDate, $endDate])
			->orderBy('date', 'asc')->get();
			foreach($empDatas as $empData){
				$empData['day'] = date('l', strtotime($empData->date));
			}
			if(count($empDatas) > 1){
				$data[] = compact('empDatas', 'startDate', 'endDate');
				\Mail::to($empDatas[0]->employee->email)->send(new MonthlyReportMail($data));
			}
			// return view('emails.MonthlyReportMail',compact('empDatas','startDate','endDate'));
			// \Mail::to($empDatas[0]->employee->email)->send(new MonthlyReportMail($data));
			// return true;
		}catch(\Exception $ex){
			dd($ex);
			session()->flash('error','Error :  Something went wrong.');
			return redirect('/');
		}
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
	public function exportExcel(Request $request)
	{
		// dd($request->all());
		try{
			$seconds = 0;
			$startDate = date("Y-m-d",strtotime(str_replace(" ","",$request->startDate)));
			$endDate = date("Y-m-d",strtotime(str_replace(" ","",$request->endDate)));
			$employee_id = $request->employee_id;
			$empDatas = AttendanceReport::where('employee_id', $employee_id)
										->whereBetween('date', [$startDate, $endDate])
										->orderBy('date', 'asc')->get();
			$empReport=[];
			$empReport[]=['#','Date','Day','Attendance','EntryTime','LateEntry','ExitTime','WorkedTime','TotalBreakTime','Thumb','OverTime','Note'];
			$i=1;
			foreach($empDatas as $empData){
				$empReport[$i]['id'] = $i;
				$empReport[$i]['date'] = date("d-m-Y",strtotime($empData->date));
				$empReport[$i]['day'] = date('l', strtotime($empData->date));
				$empReport[$i]['attendance'] = $empData->attendance;
				$empReport[$i]['officeIn'] = $empData->attendance!="Absent" ? $empData->officeIn : "Absent";
				$empReport[$i]['LE'] = $empData->attendance!="Absent" ? $empData->LE : "Absent";
				$empReport[$i]['officeOut'] = $empData->attendance!="Absent" ? $empData->officeOut : "Absent";
				$empReport[$i]['worked_time'] = $empData->attendance!="Absent" ? $empData->worked_time : "Absent";
				$empReport[$i]['total_break_time'] = $empData->attendance!="Absent" ? $empData->total_break_time : "Absent";
				$empReport[$i]['not_thumb'] = $empData->attendance!="Absent" ? $empData->not_thumb==0 ? "Not Thumb" : "OK" : "Absent";
				$empReport[$i]['OT'] = $empData->attendance!="Absent" ? $empData->OT : "Absent";
				$empReport[$i]['note'] = $empData->note;
				$i++;
			}
			\Excel::create($empDatas[0]->employee->name." Attendance Report", function($excel) use ($empReport,$empDatas,$request) {
				$excel->setTitle($empDatas[0]->employee->name." Attendance Report");
				$excel->setCreator(\Auth::user()->name)->setCompany('NKonnect Infoway Pvt. Ltd.');
				$excel->sheet('All', function($sheet)  use ($empReport,$empDatas,$request) {
					$length=count($empReport) + 8;
					$sheet->setBorder('A1:L'.$length,'thin');
					//Company Title
					$sheet->mergeCells('A1:L1');
					$sheet->getRowDimension(1)->setRowHeight(30);
					$sheet->cell('A1',$empDatas[0]->employee->company->name);
					$sheet->row(1,function($row){
						$row->setFontWeight('bold')->setFontSize(25)
						->setAlignment('center')
						->setValignment('center');
					});
					//Subject Line
					$sheet->mergeCells('A2:L2');
					$sheet->cell('A2',"Attendance Report From: ".$request->startDate." To: ".$request->endDate);
					$sheet->getRowDimension(2)->setRowHeight(25);
					$sheet->row(2,function($row){
						$row->setFontWeight('bold')->setFontSize(16)
						->setAlignment('center')
						->setValignment('center');
					});
					$sheet->mergeCells('A3:L3');
					//Employee Details
					$sheet->mergeCells("B4:C4");    $sheet->cell('B4',"Employee Name");
					$sheet->mergeCells("D4:F4");    $sheet->cell('D4',$empDatas[0]->employee->name);
					$sheet->mergeCells("G4:H4");    $sheet->cell('G4',"Department");
					$sheet->mergeCells("I4:K4");    $sheet->cell('I4',$empDatas[0]->employee->department->name);
					$sheet->getRowDimension(4)->setRowHeight(24);
					$sheet->row(4,function($row){
						$row->setFontWeight('bold')
						->setAlignment('left')
						->setValignment('center');
					});
					// $sheet->mergeCells('A5:L5');
					// Report Header Row -1
					$sheet->mergeCells('A5:C5');    $sheet->cell('A5',"Avg. Entry Time");
					$sheet->mergeCells('D5:E5');    $sheet->cell('D5',"Avg. Exit Time");
					$sheet->mergeCells('F5:G5');    $sheet->cell('F5',"Total Late Time");
					$sheet->mergeCells('H5:I5');    $sheet->cell('H5',"Total Early Time");
					$sheet->mergeCells('J5:L5');    $sheet->cell('J5',"Break Taken / Total Break");
					$sheet->getRowDimension(5)->setRowHeight(24);
					$sheet->row(5,function($row){
						$row->setFontWeight('bold')->setFontSize(12)
						->setAlignment('center')
						->setValignment('center')
						->setBackground('#fcf8e3');
					});

					$sheet->mergeCells('A6:C6');    $sheet->cell('A6',$request->avgEntry);
					$sheet->mergeCells('D6:E6');    $sheet->cell('D6',$request->avgExit);
					$sheet->mergeCells('F6:G6');    $sheet->cell('F6',$request->late);
					$sheet->mergeCells('H6:I6');    $sheet->cell('H6',$request->early);
					$sheet->mergeCells('J6:L6');    $sheet->cell('J6',$request->break);
					$sheet->getRowDimension(6)->setRowHeight(24);
					$sheet->row(6,function($row){
						$row->setFontWeight('bold')->setFontSize(12)
						->setAlignment('center')
						->setValignment('center');
					});
					// Report Header Row -2
					$sheet->mergeCells('A7:C7');    $sheet->cell('A7',"Total Duty Time");
					$sheet->mergeCells('D7:E7');    $sheet->cell('D7',"Total Worked Time");
					$sheet->mergeCells('F7:G7');    $sheet->cell('F7',"OT/LT");
					$sheet->mergeCells('H7:I7');    $sheet->cell('H7',"Total Leave");
					$sheet->mergeCells('J7:L7');    $sheet->cell('J7',"Not Thumb");
					$sheet->getRowDimension(7)->setRowHeight(24);
					$sheet->row(7,function($row){
						$row->setFontWeight('bold')->setFontSize(12)
						->setAlignment('center')
						->setValignment('center')
						->setBackground('#fcf8e3');
					});

					$sheet->mergeCells('A8:C8');    $sheet->cell('A8',$request->dutyTime);
					$sheet->mergeCells('D8:E8');    $sheet->cell('D8',$request->workedTime);
					$sheet->mergeCells('F8:G8');    $sheet->cell('F8',$request->otlt);
					$sheet->mergeCells('H8:I8');    $sheet->cell('H8',$request->leave);
					$sheet->mergeCells('J8:L8');    $sheet->cell('J8',$request->notThumb);

					$sheet->getRowDimension(8)->setRowHeight(24);
					$sheet->row(8,function($row){
						$row->setFontWeight('bold')->setFontSize(12)
						->setAlignment('center')
						->setValignment('center');
					});
					// $sheet->mergeCells('A10:L10');
					// Monthly sheet details
					$sheet->fromArray($empReport, null, 'A9', false, false);
					$sheet->row(9, function($row) {
						$row->setFontWeight('bold')
							->setAlignment('center')
							->setValignment('center')
							->setBackground('#d9edf7');
					});
					$sheet->getRowDimension(9)->setRowHeight(30);
				});
			})->download('xls');
		}catch(\Exception $ex){
			dd($ex);
			session()->flash('error','Error :  Something went wrong.');
			return redirect('/');
		}
	}
}
