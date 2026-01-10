<?php

namespace App\Http\Controllers;

use App\Models\EventType;
use App\Repositories\SelectionsRepository;
use Illuminate\Http\Request;

class SelectionsController extends Controller
{
    public function __construct(private readonly SelectionsRepository $selectionsRepository){}
    public function getSelections(Request $request): \Illuminate\Http\JsonResponse
    {
        $eventTypeId = $request->query('event_type_id');
        if ($eventTypeId) {
            return response()->json($this->selectionsRepository->getByEvent($eventTypeId));
        }

        return response()->json($this->selectionsRepository->all());
    }
    public function storeSelection(Request $request, EventType $eventType): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->selectionsRepository::store($request->input('name'), $eventType->id));

    }
}
