<?php
namespace App\Helpers;
class TimeCalc
{
    public static function sumOfTime($timeArr){
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
    public static function subOfTime($workedTime,$dutyTime){
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
        if($wseconds > $dseconds)
        $seconds = $wseconds - $dseconds;
        else
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
}
