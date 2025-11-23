<?php

namespace App\Http\Controllers;

use App\Jobs\CrawlSourceJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CrawlerController extends Controller
{

    public function start(Request $request)
    {
        $validated = $request->validate([
            'source_id' => 'required|exists:sources,id',
            'start_index' => 'integer|min:0',
        ]);

        $crawlId = DB::table('crawls')->insertGetId([
            'source_id' => $validated['source_id'],
            'start_index' => $validated['start_index'] ?? 0,
            'fetched_count' => 0,
            'status' => 'stopped',
            'message' => null,
        ]);

        CrawlSourceJob::dispatch($crawlId);

        return response()->json(['crawl_id' => $crawlId, 'message' => 'Crawl started']);
    }

    /**
     * This method is not for production.
     * @param Request $request
     * @param $sourceId
     * @param $startIndex
     * @return \Illuminate\Http\JsonResponse
     */
    public function start2(Request $request, $sourceId, $startIndex)
    {

        $crawlId = DB::table('crawls')->insertGetId([
            'source_id' => $sourceId,
            'start_index' => $startIndex,
            'fetched_count' => 0,
            'status' => 'stopped',
            'message' => null,
        ]);

        CrawlSourceJob::dispatch($crawlId);

        return response()->json(['crawl_id' => $crawlId, 'message' => 'Crawl started']);
    }

    public function status($id)
    {
        $crawl = DB::table('crawls')->where('id', $id)->first();
        if (!$crawl) {
            //abort(404);
            //throw new NotFoundHttpException('Crawler {} not found.');
            return response()->json(["message"=> "Crawler {$id} not found"], 404);
        }
        return response()->json($crawl);
    }
}
