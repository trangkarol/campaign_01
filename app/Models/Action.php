<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Nicolaslopezj\Searchable\SearchableTrait;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Traits\Eloquent\CommentLike;

class Action extends BaseModel
{
    use SoftDeletes, SearchableTrait, CommentLike;

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
    }

    protected $fillable = [
        'id',
        'caption',
        'description',
        'user_id',
        'event_id',
        'number_of_comments',
        'number_of_likes',
    ];

    protected $dates = ['deleted_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function activities()
    {
        return $this->morphMany(Activity::class, 'activitiable');
    }

    protected $searchable = [
        'columns' => [
            'actions.caption' => 10,
            'actions.description' => 10,
            'users.name' => 9,
        ],
        'joins' => [
            'users' => ['actions.user_id', 'users.id'],
        ],
    ];
}
