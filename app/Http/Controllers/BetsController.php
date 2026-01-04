<?php

namespace App\Http\Controllers;

use App\Models\Bet;
use App\Repositories\BetsRepository;
use App\Services\BetsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class BetsController extends Controller
{
    public function __construct(private readonly BetsService $betsService, private readonly BetsRepository $betsRepository){}
    public function index(): \Inertia\Response
    {
        return Inertia::render('Dashboard', [
            'bets' => $this->betsRepository->index()
        ]);
    }

    public function createBet(): \Inertia\Response
    {
        return Inertia::render('CreateBetForm');
    }

    public function storeBet(Request $request): ?\Illuminate\Http\RedirectResponse
    {
        try {
            $this->betsService->storeBet($request);
            return redirect()->route('dashboard');
        } catch (ValidationException $exception) {
            return \Redirect::back()->withErrors(['error' => $exception->getMessage()]);
        } catch (\Throwable $exception) {
            \Log::error('Error while storing bet:');
            \Log::error($exception);
            return \Redirect::back()->withErrors(['error' => 'Wystąpił błąd podczas dodawania typu']);
        }
    }

    public function changeBetStatus(Request $request, Bet $bet): ?\Illuminate\Http\RedirectResponse
    {
        try {
            $request->validate(['currentStatus' => 'required|in:pending,won,lost']);
            if ($request->input('currentStatus') === 'won') {
                $newStatus = 'lost';
            } elseif ($request->input('currentStatus') === 'lost') {
                $newStatus = 'pending';
            } else {
                $newStatus = 'won';
            }
            $this->betsRepository->changeBetStatus($bet, $newStatus);
            return Redirect::back();
        } catch (ValidationException $exception) {
            return \Redirect::back()->withErrors(['error' => 'Niepoprawne dane']);
        } catch (\Throwable $exception) {
            \Log::error('Error while changing bet status:');
            \Log::error($exception);
            return \Redirect::back()->withErrors(['error' => 'Wystąpił błąd podczas zmieniana statusu']);
        }
    }
}
