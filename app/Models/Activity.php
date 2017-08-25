<?php

namespace App\Models;

use App\Traits\Eloquent\CommentLike;

class Activity extends BaseModel
{
    use CommentLike;

    const CREATE = 'create';
    const UPDATE = 'update';
    const DELETE = 'delete';
    const JOIN = 'join';

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
    }

    protected $fillable = [
        'user_id',
        'name',
        'activitiable_id',
        'activitiable_type',
        'number_of_comments',
        'number_of_likes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function activitiable()
    {
        return $this->morphTo();
    }

    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
