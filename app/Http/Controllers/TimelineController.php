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
        $minimum_contract_value = config('timeline.minimum_contract_value');
        $organizations = $this->timelineService->getTopOrganizations();
        $organizationNames = $organizations->pluck('organization')->toArray();
        $timelineData = $this->timelineService->getTimelineData($organizationNames, $minimum_contract_value);

        return response()->json([
            'organizations' => $organizations,
            'timeline' => $timelineData
        ]);
    }
}
