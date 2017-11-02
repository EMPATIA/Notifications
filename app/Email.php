<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Email extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email_key',
        'module_token',
        'entity_key',
        'recipient',
        'subject',
        'sent',
        'content',
        'created_by',
        'updated_by',
        'newsletter_id',
        'sender_email',
        'sender_name',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['deleted_at'];

    public function queuedData(){
        return $this->hasOne('App\QueuedEmailData');
    }
}
