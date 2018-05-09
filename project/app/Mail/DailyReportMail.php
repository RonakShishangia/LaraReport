<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class DailyReportMail extends Mailable
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
        $attendanceReportDatas=$this->report;
        return $this->view('emails.dailyReportMail',compact('attendanceReportDatas'));
    }
}
