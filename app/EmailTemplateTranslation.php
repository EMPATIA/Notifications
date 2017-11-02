<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailTemplateTranslation extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email_id',
        'language_code',
        'subject',
        'header',
        'content',
        'footer'
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
    protected $hidden = ['id', 'deleted_at'];

    /**
     * Each Email Template Translation belongs to an Email Template
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function emailTemplate() {
        return $this->belongsTo('App\EmailTemplate');
    }
}
