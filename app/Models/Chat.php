<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['owner_id', 'title', 'is_group'])]
class Chat extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_group' => 'boolean',
        ];
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function participants()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('last_read_at')
            ->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class)->oldest();
    }

    public function lastMessage()
    {
        return $this->hasOne(ChatMessage::class)->latestOfMany();
    }

    public function displayNameFor(User $user): string
    {
        if ($this->is_group) {
            return $this->title ?: 'Group chat';
        }

        $otherUser = $this->participants->first(fn (User $participant) => $participant->id !== $user->id);

        return $otherUser ? $otherUser->name : 'Saved chat';
    }
}
