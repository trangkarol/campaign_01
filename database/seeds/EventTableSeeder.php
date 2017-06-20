<?php

use Illuminate\Database\Seeder;
use App\Models\Event;

class EventTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Event::truncate();
        factory(Event::class, 50)->create()->each(function($event) {
            foreach (range(1, 2) as $key) {
                $like[] = factory(App\Models\Like::class)->make()->toArray();
                $setting[] = factory(App\Models\Setting::class)->make()->toArray();
                $media[] = factory(App\Models\Media::class)->make()->toArray();
                $comments[] = factory(App\Models\Comment::class)->make()->toArray();
            }
            $event->comments()->createMany($comments);
            $event->likes()->createMany($like);
            $event->settings()->createMany($setting);
            $event->media()->createMany($media);
        });
    }
}
