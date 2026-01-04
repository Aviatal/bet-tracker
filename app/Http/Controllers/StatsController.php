<?php

namespace App\Http\Controllers;

use App\Repositories\StatsRepository;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StatsController extends Controller
{
    public function __construct(private readonly StatsRepository $statsRepository){}
    public function getStats(): \Illuminate\Http\JsonResponse
    {
        try {
            return response()->json($this->statsRepository->all());
        } catch (\Throwable $exception) {
            return response()->json(['message' => 'Wystąpił błąd podczas pobierania statystyk'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
