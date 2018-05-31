<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReceivedSms extends Model {

  use SoftDeletes;

  protected $fillable=[
    'received_sms_key',
    'entity_key',
    'site_key',
    'content',
    'sender',
    'receiver',
    'event',
    'processed',
    'answer',
    'logs',
    'service_sms_identifier',
    'service_sms_date'
  ];


    protected $dates=['deleted_at' ];


    protected $hidden =['id', 'logs', 'deleted_at'];
}
