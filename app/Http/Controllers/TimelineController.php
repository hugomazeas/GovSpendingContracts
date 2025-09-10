<?php

namespace App\Http\Controllers;

use App\Services\TimelineService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class TimelineController extends Controller
{
    public function __construct(private TimelineService $timelineService) {}

    public function index(): View
    {
        return view('timeline.index');
    }

    public function data(): JsonResponse
    {
        return response()->json($this->timelineService->getFullTimelineData());
    }
}
