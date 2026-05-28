<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Questionnaire extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Get the sections for the questionnaire.
     */
    public function sections(): HasMany
    {
        return $this->hasMany(QuestionnaireSection::class)->orderBy('order');
    }

    /**
     * Get the questions for the questionnaire through sections.
     */
    public function questions(): HasManyThrough
    {
        return $this->hasManyThrough(Question::class, QuestionnaireSection::class, 'questionnaire_id', 'section_id');
    }

    /**
     * Get the responses for the questionnaire.
     */
    public function responses(): HasMany
    {
        return $this->hasMany(QuestionnaireResponse::class);
    }

    /**
     * Get the creator of the questionnaire (Admin or BPMI).
     */
    public function createdBy(): MorphTo
    {
        return $this->morphTo('created_by');
    }
}
