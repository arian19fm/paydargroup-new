<?php

namespace Database\Factories;

use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Media> */
class MediaFactory extends Factory
{
    public function definition(): array
    {
        $name = Str::uuid().'.jpg';

        return [
            'disk' => 'public',
            'path' => 'media/2026/09/'.$name,
            'filename' => $name,
            'original_filename' => 'photo.jpg',
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'size' => 12345,
            'width' => 1200,
            'height' => 630,
            'alt_text' => $this->faker->sentence(3),
        ];
    }
}
