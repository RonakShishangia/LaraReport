<div class="panel panel-primary" >
    @if(count($empDatas) > 0)
        @php
            function sumOfTime($timeArr){
                $seconds = 0;
                foreach ($timeArr as $time){
                    list($hour,$minute,$second) = explode(':', $time);
                    $seconds += $hour*3600;
                    $seconds += $minute*60;
                    $seconds += $second;
                }
                $hours = floor($seconds/3600);
                $seconds -= $hours*3600;
                $minutes  = floor($seconds/60);
                $seconds -= $minutes*60;
                if($seconds <= 9)
                    $seconds = "0".$seconds;
                if($minutes <= 9)
                    $minutes = "0".$minutes;
                if($hours <= 9)
                    $hours = "0".$hours;
                return  "{$hours}:{$minutes}:{$seconds}";
            }
            function subOfTime($workedTime,$dutyTime){
                $wseconds=0; $dseconds=0;
                $seconds=0;
                // convert total worked time in seconds
                list($whour,$wminute,$wsecond) = explode(':', $workedTime);
                $wseconds += $whour*3600;
                $wseconds += $wminute*60;
                $wseconds += $wsecond;
                //convert total duty time in seconds
                list($dhour,$dminute,$dsecond) = explode(':', $dutyTime);
                $dseconds += $dhour*3600;
                $dseconds += $dminute*60;
                $dseconds += $dsecond;
                // substract total worked time from total dutyTime
                $seconds = $dseconds - $wseconds;
                // convert seconds into hours, minutes, and seconds
                $hours = floor($seconds/3600);
                $seconds -= $hours*3600;
                $minutes  = floor($seconds/60);
                $seconds -= $minutes*60;
                // add zero before single digits
                if($seconds <= 9)
                    $seconds = "0".abs($seconds);
                if($minutes <= 9)
                    $minutes = "0".abs($minutes);
                if($hours <= 9)
                    $hours = "0".abs($hours);
                if($wseconds > $dseconds)
                    $final=$hours.":".$minutes.":".$seconds;//"{$hours}:{$minutes}:{$seconds}";
                else{
                    $final= "-".$hours.":".$minutes.":".$seconds;//"{$hours}:{$minutes}:{$seconds}";
                }
                return  $final;
            }
        @endphp
        <div class="panel-heading">
            <b>{{$empDatas == null ? 'List' : $empDatas[0]->employee->name}}</b>
            <p class="pull-right">
                From : <b>{{ isset($startDate) ? date('d-m-Y', strtotime($startDate)) : "" }} </b>  |
                To : <b>{{ isset($startDate) ? date('d-m-Y', strtotime($endDate)) : "" }} </b>
            </p>
        </div>
        <div class="panel-body">
            <table class="table table-bordered">
                <thead>
                    <tr class="bg-info">
                        <th>NO.</th>
                        <th>Day</th>
                        <th>Date</th>
                        <th>Attendance</th>
                        <th>Entry Time</th>
                        <th>Late Entry</th>
                        <th>Exit Time</th>
                        <th>Total Time</th>
                        <th>Worked Time</th>
                        <th>Total Break Time</th>
                        <th>Thumb</th>
                        <th>OverTime</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $tempEntryTimeArr = [];     $tempExitTimeArr = [];
                        $tempTotalTimeArr = [];
                        $tempTotalWorkedTimeArr = [];
                        $tempTotalBreakTimeArr = [];    $officeBreakTotal=[];
                        $totalLeaveArr = [];
                        $tempEarlyEntryTimeArr = [];
                        $tempLateEntryTimeArr = [];
                        $tempOTTimeArr = [];
                        $tempLessOTTimeArr = [];
                        $tempDutyTime=[];
                        $tempOverTime=[];
                        $tempLessTime=[];
                        $tempNotThumb=[];
                        $tot = 0;
                    @endphp
                    @forelse($empDatas as $index => $empData)
                        <tr>
                            <td>{{$index+1}}</td>
                            <td>{{$empData->day}}</td>
                            <td>{{date('d-m-Y', strtotime($empData->date))}}</td>
                            <td class="{{$empData->attendance=="Absent" ? 'text-danger' : 'text-success'}}">{{$empData->attendance}}</td>
                            <td>{{$empData->officeIn=="00:00:00" ? "-" : $empData->officeIn}}</td>
                            <td class="{{ substr_count($empData->LE, '-') ? 'text-success' : "text-danger" }}">{{$empData->LE=="00:00:00" ? "-" : $empData->LE}}</td>
                            <td>{{$empData->officeOut=="00:00:00" ? "-" : $empData->officeOut}}</td>
                            <td>{{$empData->total_time=="00:00:00" ? "-" : $empData->total_time}}</td>
                            <td>{{$empData->worked_time=="00:00:00" ? "-" : $empData->worked_time}}</td>
                            <td>{{$empData->total_break_time=="00:00:00" ? "-" : $empData->total_break_time}}</td>
                            <td>{{ $empData->attendance!="Absent" ?  $empData->not_thumb==0 ? "Not Thumb" : "-" : "-" }}</td>
                            <td>{{ $empData->OT}}</td>
                            <td>{{$empData->note}}</td>
                        </tr>
                        @php
                            $tempTotalWorkedTimeArr[] = $empData->worked_time;
                            if($empData->day != 'Sunday'){
                                $empData->attendance != "Absent" ? $tempEntryTimeArr[] =  strtotime($empData->officeIn) : "";
                                $empData->attendance != "Absent" ? $tempExitTimeArr[] =  strtotime($empData->officeOut) : "";
                                $tempTotalTimeArr[] =  $empData->total_time;
                                $tempTotalBreakTimeArr[] = $empData->total_break_time;
                                $tempDutyTime[]=$empData->employee->company->dutyTime;
                                $empData->attendance!="Absent" ?  $officeBreakTotal[]=$empData->employee->company->breakTime : "";
                                $empData->attendance!="Absent" ?  $empData->not_thumb==0 ? $tempNotThumb[]=date('d-m-Y', strtotime($empData->date)) : "" : "";
                                if(strpos($empData->LE, '-') !== false){
                                    $EEdata = str_replace('-', '', $empData->LE);
                                    $tempEarlyEntryTimeArr[] = strtotime($EEdata);
                                }else{
                                    $LEdata = str_replace('-', '', $empData->LE);
                                    $tempLateEntryTimeArr[] =  strtotime($LEdata);
                                }

                                if ($empData->attendance=="Absent") {
                                    $totalLeaveArr[] = date('d-m-Y', strtotime($empData->date));
                                }
                            }
                        @endphp
                    @empty
                        <tr>
                            <td colspan="12" align="center"></td>
                        </tr>
                    @endforelse
                    <tfoot>
                        <tr class="bg-info">
                            <th>NO.</th>
                            <th>Day</th>
                            <th>Date</th>
                            <th>Attendance</th>
                            <th>Entry Time</th>
                            <th>Late Entry</th>
                            <th>Exit Time</th>
                            <th>Total Time</th>
                            <th>Worked Time</th>
                            <th>Total Break Time</th>
                            <th>Thumb</th>
                            <th>OverTime</th>
                            <th>Note</th>
                        </tr>
                    </tfoot>
                </tbody>
            </table>
            <legend>Report Card | <small><u>From</u> : <b>{{ isset($startDate) ? date('d - m - Y', strtotime($startDate)) : "" }}</b> <u>To</u> : <b>{{ isset($startDate) ? date('d - m - Y', strtotime($endDate)) : "" }}</b></small></legend>
            <table class="table" id="tblreport">
                @php
                    $otlt=subOfTime(sumOfTime($tempTotalWorkedTimeArr),sumOfTime($tempDutyTime));
                @endphp
                <thead class="bg-warning">
                    <tr>
                        <th>Avg. Entry Time</th>
                        <th>Avg. Exit Time</th>
                        <th>Total Late Time</th>
                        <th>Total Early Time</th>
                        <th>Break Taken / Total Break</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th>{{ date('H:i:s', array_sum($tempEntryTimeArr)/count($tempEntryTimeArr)) }}</th>
                        <th>{{ date('H:i:s', array_sum($tempExitTimeArr)/count($tempExitTimeArr)) }}</th>
                        <th>{{ date('H:i:s', array_sum($tempLateEntryTimeArr)) }}</th>
                        <th>{{ date('H:i:s', array_sum($tempEarlyEntryTimeArr)) }}</th>
                        <th>{{ sumOfTime($tempTotalBreakTimeArr)." / ".sumOfTime($officeBreakTotal)  }}</th>
                    </tr>
                </tbody>
                <thead class="bg-warning">
                    <tr>
                        <th>Total Duty Time</th>
                        <th>Total Worked Time</th>
                        <th>OT/Less Time</th>
                        <th>Total Leaves</th>
                        <th>Total Not Thumb</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th>{{ sumOfTime($tempDutyTime) }}</th>
                        <th>{{ sumOfTime($tempTotalWorkedTimeArr) }}</th>
                        <th class="{{strpos($otlt,'-')!==false ? 'text-danger' : 'text-success' }}">{{ $otlt }}</th>

                        <th>{{ count($totalLeaveArr)==0 ? '-' : count($totalLeaveArr) }}</th>
                        <th>{{ count($tempNotThumb) }}</th>

                    </tr>
                </tbody>
            </table>
        </div>
        <div class="list-group">
            <a href="#" class="list-group-item">{{$empDatas == null ? 'List' : $empDatas[0]->name}}
                <p class="pull-right">
                    From : <b>{{ isset($startDate) ? date('d-m-Y', strtotime($startDate)) : "" }} </b>  |
                    To : <b>{{ isset($startDate) ? date('d-m-Y', strtotime($endDate)) : "" }} </b>
                </p>
            </a>
        </div>
    </div>
    @endif
