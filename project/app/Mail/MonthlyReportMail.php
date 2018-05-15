<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class MonthlyReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $report;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($attendanceReportDatas)
    {
        $this->report=$attendanceReportDatas;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $empDatas=$this->report[0]['empDatas'];
        $startDate=$this->report[0]['startDate'];
        $endDate=$this->report[0]['endDate'];
        // return $this->subject(date('d-m-Y', strtotime($empDatas[0]->date))." Attendance Monthly Report Notificaton")
        return $this->view('emails.MonthlyReportMail',compact('empDatas', 'startDate', 'endDate'));
    }
}
