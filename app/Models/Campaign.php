<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends BaseModel
{
    use SoftDeletes;

    const BLOCK = 0;
    const ACTIVE = 1;
    const APPROVING = 0;
    const APPROVED = 1;

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
    }

    protected $fillable = [
        'id',
        'hashtag',
        'title',
        'description',
        'longitude',
        'latitude',
        'status',
        'address',
    ];

    protected $dates = ['deleted_at'];

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('role_id', 'status');
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    public function settings()
    {
        return $this->morphMany(Setting::class, 'settingable');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    public function actions()
    {
        return $this->hasManyThrough(Action::class, Event::class);
    }

    public function donations()
    {
        return $this->hasMany(Donation::class);
    }

    public function getUserByRole($roles)
    {
        $roles = is_array($roles) ? $roles : [$roles];
        $roleIds = [];

        foreach ($roles as $role) {
            $roleIds[] = Role::whereName($role)->first()->id;
        }

        return $this->users()->wherePivotIn('role_id', $roleIds);
    }

    public function owner()
    {
        return $this->getUserByRole('owner')->first();
    }

    public function moderators()
    {
        return $this->getUserByRole('moderator')->get();
    }

    public function members()
    {
        return $this->getUserByRole('member')->get();
    }

    public function blockeds()
    {
        return $this->getUserByRole('blocked')->get();
    }

    public function isActive()
    {
        return $this->attributes['status'] == static::ACTIVE;
    }
}
