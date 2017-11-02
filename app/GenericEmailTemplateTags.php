<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GenericEmailTemplateTags extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'generic_email_template_id',
        'tag'
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
     * Each Email Template Tag belongs to a Generic Email Template
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function genericEmailTemplate() {
        return $this->belongsTo('App\GenericEmailTemplate');
    }
}
