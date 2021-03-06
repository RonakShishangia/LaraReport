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
Auth::routes();
Route::group(['middleware' => ['auth']], function () {

    Route::match(['get', 'post'], 'register', function(){
        return redirect('/');
    });
    Route::get('/', 'AttendanceReportController@importExport');

    Route::get('importExport', 'AttendanceReportController@importExport');
    Route::get('downloadExcel/{type}', 'AttendanceReportController@downloadExcel');
    Route::post('importExcel', 'AttendanceReportController@importExcel');

    // get last month record
    Route::get('lastmonthdata/{employee_id?}', 'AttendanceReportController@lastMonthData');

    // add note to employee
    Route::post('addnote', 'AttendanceReportController@addnote')->name('addnote');

    // get all attendance data
    Route::get('attendanceData', 'AttendanceReportController@getAttendanceReport')->name('allAttendaceData');

    Route::get('searchEmp', 'AttendanceReportController@searchemp')->name('searchemp');

    Route::post('report', 'AttendanceReportController@getEmpReport')->name('report');
    Route::post('export-to-excel','AttendanceReportController@exportExcel')->name('export-to-excel');

    /* In out entry */
    Route::get('inout', function () {
        return view('inout');
    });

    Route::resource('company', 'CompanyController');
    Route::resource('department', 'DepartmentController');
    Route::resource('employee', 'EmployeeController');

    Route::get('/home', 'HomeController@index')->name('home');

});
