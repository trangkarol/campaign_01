<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Traits\Eloquent\CommentLike;

class Event extends BaseModel
{
    use SoftDeletes, CommentLike;

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
    }

    protected $fillable = [
        'id',
        'campaign_id',
        'user_id',
        'title',
        'description',
        'longitude',
        'latitude',
        'address',
        'number_of_comments',
        'number_of_likes',
    ];

    protected $dates = ['deleted_at'];

    protected $appends = [
        'slug',
    ];

    public function actions()
    {
        return $this->hasMany(Action::class);
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
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

    public function activities()
    {
        return $this->morphMany(Activity::class, 'activitiable');
    }

    public function goals()
    {
        return $this->hasMany(Goal::class);
    }

    public function donations()
    {
        return $this->hasMany(Donation::class);
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function getSlugAttribute()
    {
        return str_slug(str_limit($this->title, 100) . ' ' . $this->id);
    }
}
