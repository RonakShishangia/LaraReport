<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes;
    protected $table="companies";
    protected $fillable=['name','email','startTime','endTime','dutyTime'];
    protected $dates = ['deleted_at'];

    public function departments()
    {
        return $this->hasMany(Department::class);
    }
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
