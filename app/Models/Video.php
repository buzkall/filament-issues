<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'state', 'type', 'artist', 'video_url', 'dark_text', 'weight',
                           'publish_at', 'unpublish_at'];
    protected $casts = ['publish_at' => 'datetime',
                        'unpublish_at' => 'datetime'];
}
