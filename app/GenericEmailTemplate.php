<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GenericEmailTemplate extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'generic_email_template_key',
        'type_id'
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
     * A Generic Email Template belongs to one Type
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type(){
        return $this->belongsTo('App\Type');
    }

    /**
     * Each Generic Email Template has many Translations
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function genericEmailTemplateTranslations() {
        return $this->hasMany('App\GenericEmailTemplateTranslation');
    }

    /**
     * Each Generic Email Template has many Tags
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function genericEmailTemplateTags() {
        return $this->hasMany('App\GenericEmailTemplateTags');
    }

    /**
     * @param null $language
     * @return bool
     */
    public function translation($language = null)
    {
        $translation = $this->hasMany('App\GenericEmailTemplateTranslation')->where('language_code', '=', $language)->get();
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
        $translations = $this->hasMany('App\GenericEmailTemplateTranslation')->get()->keyBy('language_code');
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
}
