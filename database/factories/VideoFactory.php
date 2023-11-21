<?php

namespace Database\Factories;

use App\Models\Video;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class VideoFactory extends Factory
{
    protected $model = Video::class;

    public function definition(): array
    {
        return [
            'title' => fake()->word(),
            'state' => 'draft',
            'type' => fake()->word(),
            'artist' => fake()->name(),
            'video_url' => fake()->url(),
            'dark_text' => fake()->boolean(),
            'weight' => fake()->randomNumber(2),
            'publish_at' => Carbon::now(),
            'unpublish_at' => null,
        ];
    }
}
