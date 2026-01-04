<?php

namespace App\Http\Controllers;

use App\Repositories\SelectionsRepository;
use Illuminate\Http\Request;

class SelectionsController extends Controller
{
    public function __construct(private readonly SelectionsRepository $selectionsRepository){}
    public function getSelections(): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->selectionsRepository->all());
    }
    public function storeSelection(Request $request): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->selectionsRepository::store($request->input('postData.name')));

    }
}
