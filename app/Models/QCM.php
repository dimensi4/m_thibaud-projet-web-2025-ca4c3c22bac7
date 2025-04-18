<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Qcm extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'number_of_questions',
        'answers_per_question',
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    /**
     * The users who have completed this QCM.
     */
    public function completedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'qcm_results')
            ->withPivot('score', 'total_questions', 'note')
            ->withTimestamps();
    }
}
