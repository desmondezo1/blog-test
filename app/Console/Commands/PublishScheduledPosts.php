<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Post;
use Illuminate\Support\Facades\Log;

class PublishScheduledPosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'posts:publish-scheduled';
    

    /**
     * The console command description.
     *
     * @var string
     */
   
    protected $description = 'Publish scheduled posts';

    /**
     *  Find all due posts and publish them.
     */
    public function handle()
    {
        $now = now();
        info("Cron Job running at ". $now);

        $scheduledPosts = Post::where('status', 'scheduled')
            ->where('published_at', '<=', $now)
            ->get();

        foreach ($scheduledPosts as $post) {
            $post->update(['status' => 'published', 'published_at' => $now]);
            Log::info("Post ID {$post->id} has been published.");
        }

        return 0;
    }
}
