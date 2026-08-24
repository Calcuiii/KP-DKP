<?php

namespace App\Models;

use App\Notifications\ParticipantVerifyEmail;
use App\Notifications\ParticipantResetPassword;
use Database\Factories\ParticipantFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\ReplyLetter;

class Participant extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<ParticipantFactory> */
    use HasFactory, Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
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
     * @return HasMany<ParticipantApplication, $this>
     */
    public function applications(): HasMany
    {
        return $this->hasMany(ParticipantApplication::class);
    }

    /**
     * Surat balasan peserta
     */
    public function replyLetter(): HasOne
    {
        return $this->hasOne(
            ReplyLetter::class,
            'participant_id'
        );
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new ParticipantVerifyEmail);
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ParticipantResetPassword($token));
    }
}