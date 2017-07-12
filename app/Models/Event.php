<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends BaseModel
{
    use SoftDeletes;

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
    ];

    protected $dates = ['deleted_at'];
    protected $appends = ['comments'];
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

    public function getCommentsAttribute()
    {
        return $this->comments()->with('user', 'likes.user')->where('parent_id', config('settings.comment_parent'))->orderBy('created_at', 'desc')->paginate(config('settings.paginate_comment'));
    }
}
