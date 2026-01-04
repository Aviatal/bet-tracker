<?php

namespace App\Http\Controllers;

use App\Repositories\EventTypesRepository;
use Illuminate\Http\Request;

class EventTypesController extends Controller
{
    public function __construct(private readonly EventTypesRepository $eventTypesRepository){}
    public function getEventTypes(): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->eventTypesRepository->all());
    }
    public function storeEventType(Request $request): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->eventTypesRepository::store($request->input('postData.name')));

    }
}
