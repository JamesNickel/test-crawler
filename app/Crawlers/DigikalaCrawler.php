<?php

namespace App\Crawlers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DigikalaCrawler extends SourceCrawler
{
    public function crawl(int $crawlId): void
    {
        DB::table('crawls')->where('id', $crawlId)->update(['status' => 'running']);

        Log::info('Crawling ' . $this->source->base_url);

        $html = $this->fetch($this->source->base_url);
        if (!$html) {
            $this->failCrawl($crawlId, 'Failed to fetch base URL');
            return;
        }

        $dom = $this->crawler($html);

        // CSS selector for category links
        $topCategories = $dom->filter('a.styles_BaseLayoutDesktopHeaderNavigationMainMegaMenu__mainCategoriesSectionItem__Eyuvo')->each(function ($node) {
            return [
                'name' => trim($node->text()),
                'url'  => $this->makeAbsoluteUrl($node->attr('href')),
            ];
        });

        foreach ($topCategories as $cat) {
            $categoryId = $this->getOrCreateCategory($cat['name'], null);
            $this->crawlCategoryPage($categoryId, $cat['url'], $crawlId);
        }

        DB::table('crawls')->where('id', $crawlId)->update(['status' => 'completed', 'message' => $this->source->base_url]);
    }

    private function crawlCategoryPage(int $categoryId, string $url, int $crawlId): void
    {
        $page = 1;
        while (true) {
            $pageUrl = $url . (str_contains($url, '?') ? '&' : '?') . "page={$page}";
            $html = $this->fetch($pageUrl);
            if (!$html) break;

            $dom = $this->crawler($html);

            // CSS selector for product item
            $hasProducts = $dom->filter('.product-list_ProductList__item__LiiNI')->count() > 0;
            if (!$hasProducts) break;

            $dom->filter('.product-list_ProductList__item__LiiNI')->each(function ($node) use ($categoryId, $crawlId) {
                $product = [
                    'name'        => $node->filter('h3')->text(),
                    'url'         => $this->makeAbsoluteUrl($node->filter('a.styles_VerticalProductCard--hover__ud7aD')->attr('href')),
                    'external_id' => '',
                    'price'       => $this->parsePrice($node->filter('span[data-testid="price-final"]')->text()),
                    'score'       => $this->parseFloat($node->filter('p.text-body2-strong text-neutral-700')->text() ?? 0),
                    'score_count' => 0,
                ];

                $productId = $this->upsertProduct($product, $categoryId);
                $this->fetchProductDetails($productId, $product['url']);
                $this->logCrawl($crawlId, $productId, $product['url']);
            });

            $page++;
        }
    }

    private function upsertProduct(array $data, int $categoryId): int
    {
        $existing = DB::table('products')
            ->where('source_id', $this->source->id)
            ->where('external_id', $data['external_id'])
            ->first();

        $record = [
            'name'         => $data['name'],
            'price'        => $data['price'],
            'score'        => $data['score'],
            'score_count'  => $data['score_count'],
            'url'          => $data['url'],
            'category_id'  => $categoryId,
            'source_id'    => $this->source->id,
            'external_id'  => $data['external_id'],
            'is_active'    => 1,
            'updated_at'   => now(),
        ];

        if ($existing) {
            DB::table('products')->where('id', $existing->id)->update($record);
            return $existing->id;
        }

        $record['created_at'] = now();
        return DB::table('products')->insertGetId($record);
    }

    private function upsertAttribute(array $data, int $categoryId): int
    {
        $existing = DB::table('products')
            ->where('source_id', $this->source->id)
            ->where('external_id', $data['external_id'])
            ->first();

        $record = [
            'name'         => $data['name'],
            'price'        => $data['price'],
            'score'        => $data['score'],
            'score_count'  => $data['score_count'],
            'url'          => $data['url'],
            'category_id'  => $categoryId,
            'source_id'    => $this->source->id,
            'external_id'  => $data['external_id'],
            'is_active'    => 1,
            'updated_at'   => now(),
        ];

        if ($existing) {
            DB::table('products')->where('id', $existing->id)->update($record);
            return $existing->id;
        }

        $record['created_at'] = now();
        return DB::table('products')->insertGetId($record);
    }

    private function fetchProductDetails(int $productId, string $url): void
    {
        $html = $this->fetch($url);
        if (!$html) return;

        $dom = $this->crawler($html);

        // Selector for product description box
        $desc = $dom->filter('.text-body-1.text-neutral-800')->count() ? $dom->filter('.text-body-1.text-neutral-800')->text() : null;

        $attributes = [];
        $dom->filter('div.styles_SpecificationAttribute__valuesBox__gvZeQ')->each(function ($node) use (&$attributes) {
            $attributes[] = [
                'name'        => $node->filter('p.styles_SpecificationAttribute__value__CQ4Rz')->text(),
                'description' => $node->filter('p.w-full.text-body-1')->text(),
            ];
        });
        DB::table('attributes')->upsert(
            $attributes,
            ['name'],
        );
        $sellers = [];
        $dom->filter('div.styles_SellerListItemDesktop__sellerListItem__u9p3q')->each(function ($node) use (&$sellers) {
            $sellers[] = [
                'name'        => $node->filter('div.mr-4 > p')->text(),
                'score' => 0,
                'score_count' => 0,
                'external_id' => '',
            ];
        });
        DB::table('sellers')->upsert(
            $sellers,
            ['name'],
        );
        $comments = [];
        $dom->filter('article.br-list-vertical-no-padding-200')->each(function ($node) use (&$comments) {
            $comments[] = [
                'name' => $node->filter('p.whitespace-nowrap.truncate')->text(),
                'title' => null,
                'description' => $node->filter('p.text-body-1.text-neutral-900.mb-1.break-words')->text(),
                'score' => 0,
                'is_buyer' => $node->filter('p.Badge_Badge__QIekq')->count() > 0,
                'like_count' => $node->filter('div.mr-auto.lg:mr-0.flex.items-center > button:nth-child(1) p')->text(),
                'dislike_count' => $node->filter('div.mr-auto.lg:mr-0.flex.items-center > button:nth-child(2) p')->text(),
            ];
        });
        DB::table('comments')->insert($comments);
        $medias = [];
        $dom->filter('div.flex.items-center.mt-5.mb-3 > div > picture')->each(function ($node) use (&$medias) {
            $medias[] = [
                'url'  => $node->filter('source:nth-child(1)')->attr('srcset'),
                'type' => 'photo',
                'is_active' => true,
            ];
        });
        DB::table('medias')->insert($medias);

        DB::table('products')->where('id', $productId)->update([
            'description' => $desc,
            'row_data'    => json_encode(['fetched_at' => now(), 'url' => $url]),
        ]);
    }

    private function getOrCreateCategory(string $name, ?int $parentId): int
    {
        $existing = DB::table('categories')
            ->where('name', $name)
            ->where('parent_id', $parentId ?? null)
            ->first();

        if ($existing) return $existing->id;

        return DB::table('categories')->insertGetId([
            'name'       => $name,
            'parent_id'  => $parentId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function logCrawl(int $crawlId, int $productId, string $url): void
    {
        DB::table('crawl_logs')->insert([
            'crawl_id'   => $crawlId,
            'product_id' => $productId,
            'url'        => $url,
        ]);

        DB::table('crawls')->where('id', $crawlId)->increment('fetched_count');
    }

    private function failCrawl(int $crawlId, string $message): void
    {
        DB::table('crawls')
            ->where('id', $crawlId)
            ->update(['status' => 'failed', 'message' => $message]);
    }

    private function parsePrice(string $text): ?float
    {
        return (float) preg_replace('/[^0-9.]/', '', $text) ?: null;
    }

    private function parseFloat($value): ?float
    {
        return is_numeric($value) ? (float)$value : null;
    }

    private function parseInt($value): int
    {
        return (int) preg_replace('/\D/', '', $value ?? '');
    }
}
