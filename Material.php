<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    protected $fillable = [
    'title',
    'slug',
    'description',
    'type',
    'file_path',
    'thumbnail',
    'user_id',
    'is_approved',
];
public function comments()
{
    return $this->hasMany(Comment::class)->whereNull('parent_id')->latest();
}
}

