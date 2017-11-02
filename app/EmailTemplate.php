<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailTemplate extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email_template_key',
        'type_id',
        'email_group_id'
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
     * An Email Template belongs to one Email Group
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function emailGroup(){
        return $this->belongsTo('App\EmailGroup');
    }

    /**
     * An Email Template belongs to one Type
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type(){
        return $this->belongsTo('App\Type');
    }

    /**
     * Each Email has many Translations
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function emailTemplateTranslations() {
        return $this->hasMany('App\EmailTemplateTranslation');
    }

    /**
     * @param null $language
     * @return bool
     */
    public function translation($language = null)
    {
        $translation = $this->hasMany('App\EmailTemplateTranslation')->where('language_code', '=', $language)->get();
        if(sizeof($translation)>0){
            $this->setAttribute('subject',$translation[0]->subject);
            $this->setAttribute('header',html_entity_decode($translation[0]->header));
            $this->setAttribute('content',html_entity_decode($translation[0]->content));
            $this->setAttribute('footer',html_entity_decode($translation[0]->footer));
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return mixed
     */
    public function translations()
    {
        $translations = $this->hasMany('App\EmailTemplateTranslation')->get()->keyBy('language_code');
        if(sizeof($translations)>0){
            foreach ($translations as $translation){
                $translation['subject'] = html_entity_decode($translation['subject']);
                $translation['header'] = html_entity_decode($translation['header']);
                $translation['content'] = html_entity_decode($translation['content']);
                $translation['footer'] = html_entity_decode($translation['footer']);
            }
        };
        $this->setAttribute('translations',$translations);
        return $translations;
    }

    public function getAllTranslations() {
        return $this->hasMany('App\EmailTemplateTranslation');
    }
}
