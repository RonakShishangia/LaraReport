<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/', 'AttendanceReportController@importExport');

Route::get('importExport', 'AttendanceReportController@importExport');
Route::get('downloadExcel/{type}', 'AttendanceReportController@downloadExcel');
Route::post('importExcel', 'AttendanceReportController@importExcel');
// get all attendance data
Route::get('attendanceData', 'AttendanceReportController@getAttendanceReport')->name('allAttendaceData');

/* In out entry */
Route::get('inout', function () {
    return view('inout');
});