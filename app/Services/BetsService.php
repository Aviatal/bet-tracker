<?php

namespace App\Services;

use App\Repositories\BetsRepository;
use Illuminate\Http\Request;

class BetsService
{
    public function __construct(private readonly BetsRepository $betsRepository){}
    public function storeBet(Request $request): \App\Models\Bet
    {
        $data = $this->validateStoreRequest($request);
        return $this->betsRepository->createBet($data);
    }
    private function validateStoreRequest(Request $request) :array
    {
        $request->merge(['home_id' => $request->input('home.id')]);
        $request->merge(['away_id' => $request->input('away.id')]);
        $request->merge(['discipline_id' => $request->input('discipline.id')]);
        $request->merge(['event_type_id' => $request->input('event_type.id')]);
        $request->merge(['selection_id' => $request->input('selection.id')]);
        unset($request['home'], $request['away'], $request['discipline'], $request['event_type'], $request['selection']);
        $request->validate([
            'home_id' => ['required', 'integer', 'exists:competitors,id'],
            'away_id' => ['required', 'integer', 'exists:competitors,id'],
            'discipline_id' => ['required', 'integer', 'exists:disciplines,id'],
            'event_type_id' => ['required', 'integer', 'exists:event_types,id'],
            'selection_id' => ['required', 'integer', 'exists:selections,id'],
            'event_date' => ['required', 'date'],
            'stake' => ['required', 'numeric'],
            'odds' => ['required', 'numeric'],
        ], [
            'required' => 'Pole :attribute jest wymagane.',
            'integer' => 'Pole :attribute ma niepoprawny format.',
            'numeric' => 'Pole :attribute ma niepoprawny format.',
            'exists' => 'Pole :attribute ma wartość nieistniejącą w bazie danych.'
        ], [
            'home_id' => 'gospodarz',
            'away_id' => 'gość',
            'discipline_id' => 'dyscyplina',
            'event_type_id' => 'rodzaj zakładu',
            'selection_id' => 'typ',
            'stake' => 'stawka',
            'odds' => 'kurs',
        ]);
        return $request->all();
    }
}
