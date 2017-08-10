<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Traits\Eloquent\CommentLike as CommentLike;

class Comment extends BaseModel
{
    use SoftDeletes, CommentLike;

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
    }

    protected $fillable = [
        'id',
        'user_id',
        'content',
        'parent_id',
        'number_of_comments',
        'number_of_likes',
        'commentable_id',
        'commentable_type',
    ];

    protected $dates = ['deleted_at'];

    protected $appends = [
        'user',
        'checkLike',
    ];

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

    public function getUserAttribute()
    {
        return $this->user()->first();
    }

    public function getCheckLikeAttribute()
    {
        return !is_null($this->likes()->where('user_id', \Auth::guard('api')->user()->id)->first());
    }
}
