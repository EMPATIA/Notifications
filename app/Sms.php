<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Sms extends Model
{

  use SoftDeletes;

  protected $fillable=[
    'sms_key',
    'module_token',
    'sent',
    'recipient',
    'content',
      'service',
      'error_log',
    'site_key',
    'created_by',
    'updated_by'
  ];


    protected $dates=['deleted_at' ];


    protected $hidden =['id', 'deleted_at'];
}
