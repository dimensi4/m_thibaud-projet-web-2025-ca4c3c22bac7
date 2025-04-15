<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QCM extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'theme',
        'questions',
        'user_id'
    ];

    protected $casts = [
        'questions' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
