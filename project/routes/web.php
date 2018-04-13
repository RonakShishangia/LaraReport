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

// add note to employee
Route::post('addnote', 'AttendanceReportController@addnote')->name('addnote');

// get all attendance data
Route::get('attendanceData', 'AttendanceReportController@getAttendanceReport')->name('allAttendaceData');

Route::get('searchEmp', 'AttendanceReportController@searchemp')->name('searchemp');

Route::post('report', 'AttendanceReportController@getEmpReport')->name('report');

/* In out entry */
Route::get('inout', function () {
    return view('inout');
});
Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');


