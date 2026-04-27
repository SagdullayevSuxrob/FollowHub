<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'username',
        'name',
        'email',
        'type',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Get the users that this user is following
    public function following()
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'following_id')
            ->withPivot('status');
    }

    // Get the users that are following this user
    public function followers()
    {
        return $this->belongsToMany(User::class, 'follows', 'following_id', 'follower_id')
            ->withPivot('status');
    }

    // Get the users that are friends with this user
    public function friends()
    {
        return $this->following()->whereHas('following', function ($query) {
            $query->where('following_id', $this->id);
        });
    }

    // Check if the user is following another user
    public function isFollowing(User $user)
    {
        return $this->following()
            ->wherePivot('status', 'accepted')
            ->where('following_id', $user->id)
            ->exists();
    }

    // Get follow status.
    public function getFollowStatus(User $user, $me)
    {
        if (!$me) return 'You are not logged in'; // Agar foydalanuvchi tizimga kirmagan bo'lsa
        if ($user->id === $me->id) return 'self';

        if ($me->blockedUsers()->where('blocked_id', $user->id)->exists()) return "unblock";
        if ($me->blockedBy()->where('blocker_id', $user->id)->exists()) return "blocked by user";

        $followRecord = $me->following()->withPivot('status')->where('following_id', $user->id)->first();
        $followerRecord = $me->followers()->withPivot('status')->where('follower_id', $user->id)->first();

        if (
            $followerRecord && $followerRecord->pivot->status === 'accepted' &&
            $followRecord && $followRecord->pivot->status === 'accepted'
        ) {
            return "friend";
        }

        if ($followRecord && $followRecord->pivot->status === 'requested') {
            return "requested";
        }

        if ($followRecord && $followRecord->pivot->status === 'accepted') {
            return "following";
        }

        if ($followerRecord && $followerRecord->pivot->status === 'accepted') {
            return "follow back";
        }

        return 'follow';
    }

    // The account is private
    public function isPrivate(User $me)
    {
        if ($this->id === $me->id) {
            return false;
        }

        if ($this->type === 'private' && !$me->isFollowing($this)) {
            return true;
        }
        return false;
    }

    public function blockedUsers()
    {
        return $this->belongsToMany(User::class, 'blocks', 'blocker_id', 'blocked_id');
    }

    public function blockedBy()
    {
        return $this->belongsToMany(User::class, 'blocks', 'blocked_id', 'blocker_id');
    }

    public function Blocked(User $me)
    {
        if ($this->blockedUsers()->where('blocked_id', $me->id)->exists()) {
            return true;
        }
        return false;
    }

    public function isBlocked(User $me)
    {
        if ($this->blockedBy()->where('blocker_id', $me->id)->exists()) {
            return true;
        }
        return false;
    }

    public function block($userId)
    {
        return $this->blockedUsers()->attach([$userId]);
    }

    public function unblock(User $user)
    {
        return $this->blockedUsers()->detach($user->id);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function views()
    {
        return $this->hasMany(PostView::class);
    }

    public function like()
    {
        return $this->hasMany(Like::class);
    }
}
