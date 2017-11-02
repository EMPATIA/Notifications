<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Type extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type_key',
        'code'
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
     * Each Type has many EmailTemplates
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function emailTemplates() {
        return $this->hasMany('App\EmailTemplate');
    }

    /**
     * Each Type has many Translations
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function typeTranslations() {
        return $this->hasMany('App\TypeTranslation');
    }

    /**
     * @param null $language
     * @return bool
     */
    public function translation($language = null)
    {
        $translation = $this->hasMany('App\TypeTranslation')->where('language_code', '=', $language)->get();
        if(sizeof($translation)>0){
            $this->setAttribute('name',$translation[0]->name);
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
        $translations = $this->hasMany('App\TypeTranslation')->get();
        $this->setAttribute('translations',$translations);
        return $translations;
    }

    public function newTranslation($language = null, $languageDefault = null) {
        $translation = $this->hasMany('App\TypeTranslation')->orderByRaw("FIELD(language_code,'".$languageDefault."','".$language."')DESC")->first();
        $this->setAttribute('name',$translation->name ?? null);
        $this->setAttribute('description',$translation->description ?? null);
    }

    public function tags() {
        return $this->hasMany('App\Tag');
    }
}
