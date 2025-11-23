<?php

namespace App\Jobs;

use App\Models\Crawl;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class CrawlSourceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $crawlId;

    public function __construct($crawlId)
    {
        $this->crawlId = $crawlId;
    }

    public function handle(): void
    {
        DB::table('crawls')->where('id', $this->crawlId)->update(['status' => 'running']);

        try {
            $crawl  = DB::table('crawls')->where('id', $this->crawlId)->first();
            $source = DB::table('sources')->where('id', $crawl->source_id)->first();

            $crawlerClass = config('crawlers.' . strtolower($source->name));
            if (!class_exists($crawlerClass)) {
                throw new \Exception("Crawler class not found: {$source->name}");
            }

            $crawler = new $crawlerClass($source); // stdClass with id, name, base_url
            $crawler->crawl($this->crawlId);

        } catch (\Throwable $e) {
            DB::table('crawls')->where('id', $this->crawlId)->update([
                'status'  => 'failed',
                'message' => $e->getMessage()
            ]);
        }
    }
}
