<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'qcm_id',
        'question_text',
        'difficulty',
        'correct_answer_id',
    ];

    public function qcm(): BelongsTo
    {
        return $this->belongsTo(Qcm::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }
}
