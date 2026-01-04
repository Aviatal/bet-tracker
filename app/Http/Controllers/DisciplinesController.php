<?php

namespace App\Http\Controllers;

use App\Repositories\DisciplinesRepository;
use Illuminate\Http\Request;

class DisciplinesController extends Controller
{
    public function __construct(private readonly DisciplinesRepository $disciplinesRepository){}
    public function getDisciplines(): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->disciplinesRepository->all());
    }

    public function storeDiscipline(Request $request): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->disciplinesRepository->store($request->input('postData.name')));
    }
}
