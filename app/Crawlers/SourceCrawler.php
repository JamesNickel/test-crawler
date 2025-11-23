<?php

namespace App\Crawlers;

use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

abstract class SourceCrawler
{
    /** @var object Source data from DB (stdClass or array) */
    protected $source;

    /**
     * Expected properties: id, name, base_url
     */
    public function __construct(object $source)
    {
        $this->source = $source;
    }

    /**
     * Main entry point called from the job
     * @param int $crawlId
     */
    abstract public function crawl(int $crawlId): void;

    protected function makeAbsoluteUrl(string $url): string
    {
        if (empty($url)) {
            return $url;
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        if (str_starts_with($url, '//')) {
            return 'https:' . $url;
        }

        $base = rtrim($this->source->base_url, '/');
        if (str_starts_with($url, '/')) {
            return $base . $url;
        }

        return $base . '/' . $url;
    }

    protected function fetch(string $url, array $options = []): ?string
    {
        try {
            $response = Http::timeout(30)
                ->retry(3, 1000)
                ->get($url, $options);

            if ($response->successful()) {
                return $response->body();
            }
        } catch (\Throwable $e) {
            // You can log here if you want
        }

        return null;
    }

    protected function crawler(string $html): DomCrawler
    {
        return new DomCrawler($html, $this->source->base_url);
    }
}
