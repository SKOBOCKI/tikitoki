<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['follower_id', 'creator_id'])]
class Subscription extends Model
{
    public function follower()
    {
        return $this->belongsTo(User::class, 'follower_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
}
