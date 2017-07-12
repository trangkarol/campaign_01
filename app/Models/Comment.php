<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends BaseModel
{
    use SoftDeletes;

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
    }

    protected $fillable = [
        'id',
        'user_id',
        'content',
        'parent_id',
        'commentable_id',
        'commentable_type',
    ];

    protected $dates = ['deleted_at'];
    protected $appends = ['sub_comment', 'likes', 'user'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function commentable()
    {
        return $this->morphTo();
    }

    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    public function activities()
    {
        return $this->morphMany(Activity::class, 'activitiable');
    }

    public function subComment()
    {
        return $this->hasMany(Comment::class, 'parent_id', 'id');
    }

    public function getSubCommentAttribute()
    {
        return $this->subComment()->with('user', 'likes.user')->orderBy('created_at', 'desc')->paginate(config('settings.paginate_comment'));
    }

    public function getLikesAttribute()
    {
        return $this->likes()->with('user')->get();
    }

    public function getUserAttribute()
    {
        return $this->user()->first();
    }
}
