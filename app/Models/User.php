<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'last_name',
        'first_name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * This function returns the full name of the connected user
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        return $this->last_name . ' ' . $this->first_name;
    }

    /**
     * This function returns the short name of the connected user
     * @return string
     */
    public function getShortNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name[0] . '.';
    }

    /**
     * Retrieve the school of the user
     *
     * @return (Model&object)|null
     */
    public function school()
    {
        // With this, the user can only have 1 school
        return $this->belongsToMany(School::class, 'users_schools')
            ->withPivot('role')
            ->first();
    }

    /**
     * Retrieve all tasks assigned to this user with pivot info
     *
     * @return BelongsToMany
     */
    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class)
            ->withPivot('completed_at', 'comment')
            ->withTimestamps();
    }

    /**
     * Retrieve only the tasks that the user has marked as completed
     *
     * @return BelongsToMany
     */
    public function completedTasks(): BelongsToMany
    {
        return $this->tasks()->wherePivotNotNull('completed_at');
    }

    /**
     * The qcms that the user has completed.
     */
    public function qcms(): BelongsToMany
    {
        return $this->belongsToMany(Qcm::class, 'qcm_results')
            ->withPivot('score', 'total_questions', 'note')
            ->withTimestamps();
    }
}
