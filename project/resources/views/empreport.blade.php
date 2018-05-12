<div class="panel panel-primary" >
        <!-- Default panel contents -->
    @if(count($empDatas) > 0)

    @php
    function sumOfTime($timeArr){
        $seconds = 0;
        foreach ($timeArr as $time)
        {
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
    function subOfTime($worked,$duty){
        $start    = explode(":",$worked);
        $end  = explode(":",$duty);
        $start = new DateInterval('PT'.$start[0]."H".$start[1]."M".$start[2]."S");
        $end = new DateInterval('PT'.$end[0]."H".$end[1]."M".$end[2]."S");
        $startTime=new DateTime();
        $startTime->add($start);
        $endTime=new DateTime();
        $endTime->add($end);
        $diff=$endTime->diff($startTime);
        $final = $diff->format("%H:%I:%S");
        return $endTime > $startTime ? "-".$final : $final;
        echo $final;
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
                        $tempEntryTimeArr = [];
                        $tempExitTimeArr = [];
                        $tempTotalTimeArr = [];
                        $tempTotalWorkedTimeArr = [];
                        $tempTotalBreakTimeArr = [];
                        $tempTotalBreakArr = [];
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
                            <td class="{{$empData->attendance=="Absent" ? 'text-danger' : 'text-success'}}">{{ ($empData->day=="Sunday") ?  (($empData->attendance=="Present") ? "Present On Weekly Off" : "On WeeklyOff") : $empData->attendance}}</td>
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
                            if($empData->day != "Sunday"){
                                $empData->attendance != "Absent" ? $tempEntryTimeArr[] =  strtotime($empData->officeIn) : "";
                                $empData->attendance != "Absent" ? $tempExitTimeArr[] =  strtotime($empData->officeOut) : "";
                                $tempTotalTimeArr[] =  $empData->total_time;
                                $tempTotalBreakTimeArr[] = $empData->total_break_time;
                                $tempDutyTime[]=$empData->employee->company->dutyTime;
                            }

                            strpos($empData->OT,'-')!==false ? $tempLessTime[]=str_replace('-', '', $empData->OT) : $tempOverTime[]=$empData->OT;

                            $empData->attendance!="Absent" ?  $empData->not_thumb==0 ? $tempNotThumb[]=date('d-m-Y', strtotime($empData->date)) : "" : "";

                            if(strpos($empData->LE, '-') !== false){
                                $EEdata = str_replace('-', '', $empData->LE);
                                $tempEarlyEntryTimeArr[] = strtotime($EEdata);
                            }else{
                                $LEdata = str_replace('-', '', $empData->LE);
                                $tempLateEntryTimeArr[] =  strtotime($LEdata);
                            }

                            if ($empData->attendance=="Absent") {
                                $tempTotalBreakArr[] = date('d-m-Y', strtotime($empData->date));
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
                <thead class="bg-warning">
                    <tr>
                        <th>Avg. Entry Time</th>
                        <th>Avg. Exit Time</th>
                        <th>Total Late Time</th>
                        <th>Total Early Time</th>
                        <th>Total Break Time</th>
                        <th>Total Time</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th>{{ date('H:i:s', array_sum($tempEntryTimeArr)/count($tempEntryTimeArr)) }}</th>
                        <th>{{ date('H:i:s', array_sum($tempExitTimeArr)/count($tempExitTimeArr)) }}</th>
                        <th>{{ date('H:i:s', array_sum($tempLateEntryTimeArr)) }}</th>
                        <th>{{ date('H:i:s', array_sum($tempEarlyEntryTimeArr)) }}</th>
                        <th>{{ sumOfTime($tempTotalBreakTimeArr) }}</th>
                        <th>{{ sumOfTime($tempTotalTimeArr) }}</th>

                    </tr>
                </tbody>
                <thead class="bg-warning">
                    <tr>
                        <th>Total Duty Time</th>
                        <th>Total Worked Time</th>
                        {{-- <th>OT/Less Time</th> --}}
                        <th>Total Leaves</th>
                        <th>Total Not Thumb</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th>{{ sumOfTime($tempDutyTime) }}</th>
                        <th>{{ sumOfTime($tempTotalWorkedTimeArr) }}</th>
                        {{-- <th>{{ sumOfTime($tempOverTime) }}</th> --}}
                        {{-- <th>{{ subOfTime(sumOfTime($tempTotalWorkedTimeArr),sumOfTime($tempDutyTime)) }}</th> --}}
                        {{-- <th>{{ subOfTime(sumOfTime($tempOverTime),sumOfTime($tempLessTime)) }}</th> --}}
                        <th>{{ count($tempTotalBreakArr)==0 ? '-' : count($tempTotalBreakArr) }}</th>
                        <th>{{ count($tempNotThumb) }}</th>
                        {{-- <th>{{ print_r($tempOverTime) }}</th>
                        <th>{{ print_r($tempLessTime) }}</th> --}}
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- List group -->
        <div class="list-group">
            {{-- {{ceil(array_sum($ExitTimeArr)/count($ExitTimeArr))}} --}}
            <a href="#" class="list-group-item">{{$empDatas == null ? 'List' : $empDatas[0]->name}}
                <p class="pull-right">
                    From : <b>{{ isset($startDate) ? date('d-m-Y', strtotime($startDate)) : "" }} </b>  |
                    To : <b>{{ isset($startDate) ? date('d-m-Y', strtotime($endDate)) : "" }} </b>
                </p>
            </a>
        </div>
    </div>
    @endif
