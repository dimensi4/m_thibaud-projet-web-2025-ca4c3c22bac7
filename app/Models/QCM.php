<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
}
