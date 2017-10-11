<?php

namespace App\Models;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Eloquent\UrlImage;
use App\Models\Role;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Traits\Eloquent\ConvertEmptyStringToNull;
use App\Notifications\ResetPassword;
use Nicolaslopezj\Searchable\SearchableTrait;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes, UrlImage, ConvertEmptyStringToNull, SearchableTrait;

    // User's status
    const IN_ACTIVE = 0;
    const ACTIVE = 1;
    const BAN = 2;
    const ACTIVE_LINK_SEND = 'emails.active';

    // Relationship status
    const PENDING = 0;
    const ACCEPTED = 1;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'birthday',
        'url_file',
        'address',
        'phone',
        'status',
        'token_confirm',
        'head_photo',
        'gender',
        'about',
        'deleted_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'friends_count',
        'is_friend',
        'has_pending_request',
        'has_send_request',
    ];

    protected $dates = ['deleted_at'];

    protected $appends = [
        'image_default',
        'image_thumbnail',
        'image_small',
        'image_medium',
        'image_large',
        'default_header',
        'friends_count',
        'is_friend',
        'has_pending_request',
        'has_send_request',
        'slug',
    ];

    protected $searchable = [
        'columns' => [
            'users.name' => 9,
            'users.email' => 10,
        ],
    ];

    protected $casts = [
        'is_friend' => 'boolean',
    ];

    public function actions()
    {
        return $this->hasMany(Action::class);
    }

    public function campaigns()
    {
        return $this->belongsToMany(Campaign::class)
            ->withPivot('role_id', 'status', 'deleted_at')
            ->withTimestamps();
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function donations()
    {
        return $this->hasMany(Donation::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    public function friendsIAmSender()
    {
        return $this->belongsToMany(User::class, 'relationships', 'sender_id', 'recipient_id')
            ->where('users.status', self::ACTIVE)
            ->withTimestamps()
            ->withPivot('status');
    }

    public function friendsIAmRecipient()
    {
        return $this->belongsToMany(User::class, 'relationships', 'recipient_id', 'sender_id')
            ->where('users.status', self::ACTIVE)
            ->withTimestamps()
            ->withPivot('status');
    }

    public function friends($select = ['users.*'])
    {
        $friends = collect();
        $friends->push($this->friendsIAmSender()
            ->wherePivot('status', self::ACCEPTED)
            ->select($select)
            ->withCount([
                'friendsIAmSender as sender' => function ($query) {
                    $query->where('relationships.status', self::ACCEPTED);
                },
                'friendsIAmRecipient as recipient' => function ($query) {
                    $query->where('relationships.status', self::ACCEPTED);
                },
                'media AS photos' => function ($query) {
                    $query->where('type', Media::IMAGE);
                },
                'media AS videos' => function ($query) {
                    $query->where('type', Media::VIDEO);
                },
            ])->get());
        $friends->push($this->friendsIAmRecipient()
            ->wherePivot('status', self::ACCEPTED)
            ->select($select)
            ->withCount([
                'friendsIAmSender as sender' => function ($query) {
                    $query->where('relationships.status', self::ACCEPTED);
                },
                'friendsIAmRecipient as recipient' => function ($query) {
                    $query->where('relationships.status', self::ACCEPTED);
                },
                'media AS photos' => function ($query) {
                    $query->where('type', Media::IMAGE);
                },
                'media AS videos' => function ($query) {
                    $query->where('type', Media::VIDEO);
                },
            ])->get());
        $friends = $friends->flatten()->unique('id')->values();

        return $friends;
    }

    public function getFriendsCountAttribute()
    {
        return $this->friends()->count();
    }

    public function getIsFriendAttribute()
    {
        $id = auth()->guard('api')->id();
        return $id ? $this->isFriendWith(auth()->guard('api')->id()) : false;
    }

    public function getHasPendingRequestAttribute()
    {
        $id = auth()->guard('api')->id();
        return $id ? $this->hasPendingRequestFrom($id) : false;
    }

    public function getHasSendRequestAttribute()
    {
        $id = auth()->guard('api')->id();
        return $id ? $this->hasSendRequestTo($id) : false;
    }

    public function pendingFriends()
    {
        return $this->friendsIAmRecipient()
            ->wherePivot('status', self::PENDING);
    }

    public function acceptFriends()
    {
        return $this->friendsIAmSender()
            ->wherePivot('status', self::PENDING);
    }

    public function isFriendWith($id)
    {
        return $this->friends()->where('id', $id)->count();
    }

    public function hasPendingRequestFrom($id)
    {
        return $this->friendsIAmRecipient()
            ->where('users.id', $id)
            ->wherePivot('status', self::PENDING)
            ->exists();
    }

    public function hasSendRequestTo($id)
    {
        return $this->friendsIAmSender()
            ->where('users.id', $id)
            ->wherePivot('status', self::PENDING)
            ->exists();
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }

    public function hasRole($role)
    {
        $roleId = Role::whereName($role)->first()->id;

        return $this->roles()->wherePivot('role_id', $roleId)->exists();
    }

    public function getAbout($value = null)
    {
        if (!$value || !is_numeric($value)) {
            return $this->attributes['about'];
        }

        return str_limit($this->attributes['about'], $value);
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function settings()
    {
        return $this->morphMany(Setting::class, 'settingable');
    }

    public function getDefaultHeaderAttribute()
    {
        return $this->factoryImage($this->head_photo);
    }

    protected static function boot()
    {
        static::creating(function ($user) {
            // Set filtering params before creating that it executes saving
            $user->token_confirm = Str::random(60);
            $user->birthday = is_null($user->birthday) ? null : Carbon::parse($user->birthday);
            $user->url_file = $user->url_file ?: config('settings.default_avatar');
        });
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    public function getBirthdayAttribute($date)
    {
        return Carbon::parse($date)->toDateString();
    }

    public function getSlugAttribute()
    {
        return str_slug(str_limit($this->name, 100) . ' ' . $this->id);
    }
}
