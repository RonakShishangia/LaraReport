<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;
       
    protected $table="employees";
	protected $fillable=['name','password','department_id','email','contact','chat_id'];
	protected $dates = ['deleted_at'];
	
    public function department()
    {
        return $this->belongsTo('App\Department');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
