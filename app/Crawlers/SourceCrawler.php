<?php

namespace App\Crawlers;

use Illuminate\Support\Facades\DB;
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

    protected function insertDummyData(){
        DB::table('categories')->upsert([
            ['name'=> 'گوشی', 'parent_id'=> null],
            ['name'=> 'لپتاپ', 'parent_id'=> null],
        ], ['name']);
        DB::table('attributes')->insert([
            [
                'name'=> 'نوع گوشی موبایل',
                'description'=> 'سیستم عامل اندروید',
            ],
        ]);
        DB::table('products')->upsert([
            [
                'name'=> 'گوشی موبایل سامسونگ مدل Galaxy A07 دو سیم کارت ظرفیت 128 گیگابایت و رم 4 گیگابایت',
                'description'=> '',
                'score'=> 4.4,
                'score_count'=> 181,
                'price'=> 10900000,
                'is_active'=>true,
                'source_id'=>1,
                'category_id'=>1,
                'url'=>'',
            ],
        ], ['source_id', 'external_id']);
        DB::table('medias')->upsert([
            [
                'url'=> 'https://dkstatics-public.digikala.com/digikala-products/69c8ee8dcb6d825fdb6de8a8515b2a45b4fb7a79_1763385430.jpg?x-oss-process=image/resize,m_lfit,h_800,w_800/quality,q_90',
                'type'=> 'photo',
                'product_id'=> 1,
            ],
        ], ['url']);
        DB::table('sellers')->upsert([
            [
                'name'=> 'هماهنگ شاپ',
                'source_id'=> 1,
            ],
        ], ['name']);
        DB::table('product_seller')->insert([
            [
                'product_id'=> 1,
                'seller_id'=> 1,
            ],
        ]);
    }
}
