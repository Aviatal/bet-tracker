<?php

namespace App\Http\Controllers;

use App\Repositories\CompetitorsRepository;
use Illuminate\Http\Request;

class CompetitorsController extends Controller
{
    public function __construct(private readonly CompetitorsRepository $competitorsRepository){}
    public function getCompetitors(): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->competitorsRepository->all());
    }
    public function storeCompetitor(Request $request): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->competitorsRepository->store($request->input('postData.name')));

    }
}
