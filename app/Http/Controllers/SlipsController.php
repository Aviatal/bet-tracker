<?php

namespace App\Http\Controllers;

use App\Models\Slip;
use App\Repositories\SlipsRepository;
use App\Services\SlipsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class SlipsController extends Controller
{
    public function __construct(private readonly SlipsService $slipsService, private readonly SlipsRepository $slipsRepository){}
    public function index(): \Inertia\Response
    {
        return Inertia::render('Dashboard', [
            'slips' => $this->slipsRepository->index()
        ]);
    }

    public function createSlip(): \Inertia\Response
    {
        return Inertia::render('CreateSlipForm');
    }

    public function storeSlip(Request $request): ?\Illuminate\Http\RedirectResponse
    {
        try {
            $this->slipsService->storeSlip($request);
            return redirect()->route('dashboard');
        } catch (ValidationException $exception) {
            return \Redirect::back()->withErrors(['error' => $exception->getMessage()]);
        } catch (\Throwable $exception) {
            \Log::error('Error while storing slip:');
            \Log::error($exception);
            return \Redirect::back()->withErrors(['error' => 'Wystąpił błąd podczas dodawania typu']);
        }
    }

    public function changeSlipStatus(Request $request, Slip $slip): ?\Illuminate\Http\RedirectResponse
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
            $this->slipsRepository->changeSlipStatus($slip, $newStatus);
            return Redirect::back();
        } catch (ValidationException $exception) {
            return \Redirect::back()->withErrors(['error' => 'Niepoprawne dane']);
        } catch (\Throwable $exception) {
            \Log::error('Error while changing slip status:');
            \Log::error($exception);
            return \Redirect::back()->withErrors(['error' => 'Wystąpił błąd podczas zmieniana statusu']);
        }
    }

    public function updateSlipStake(Request $request, Slip $slip): \Illuminate\Http\RedirectResponse
    {
        try {
            $request->validate(['stake' => 'required|numeric|min:0.01']);
            $this->slipsRepository->updateSlipStake($slip, $request->input('stake'));
            return Redirect::back();
        } catch (\Throwable $exception) {
            return \Redirect::back()->withErrors(['error' => 'Wystąpił błąd podczas edycji stawki']);
        }
    }

    public function deleteSlip(Slip $slip): ?\Illuminate\Http\RedirectResponse
    {
        try {
            $this->slipsRepository->deleteSlip($slip);
            return Redirect::back();
        } catch (\Throwable $exception) {
            return \Redirect::back()->withErrors(['error' => 'Wystąpił błąd podczas usuwania kuponu']);
        }
    }
}
