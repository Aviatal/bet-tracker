<?php

namespace App\Services;

use App\Models\Slip;
use App\Models\User;
use App\Repositories\SlipsRepository;
use Illuminate\Http\Request;
use Mail;

readonly class SlipsService
{
    public function __construct(private SlipsRepository $slipsRepository){}
    public function storeSlip(Request $request): \App\Models\Slip
    {
        $data = $this->validateStoreRequest($request);
        $slip = $this->slipsRepository->createSlip($data);
        $this->sendSlipToEmail($slip);
        return $slip;
    }
    private function validateStoreRequest(Request $request) :array
    {
        $events = [];
        foreach ($request->input('events') as $event) {
            $event['home_id'] = $event['home'][0]['id'];
            $event['away_id'] = $event['away'][0]['id'];
            $event['discipline_id'] = $event['discipline'][0]['id'];
            $event['event_type_id'] = $event['event_type'][0]['id'];
            $event['selection_id'] = $event['selection'][0]['id'];
            unset($event['home'], $event['away'], $event['discipline'], $event['event_type'], $event['selection']);
            $events[] = $event;
        }
        $request->merge(['events' => $events]);
        $request->validate([
            'events.*.home_id' => ['required', 'integer', 'exists:competitors,id'],
            'events.*.away_id' => ['required', 'integer', 'exists:competitors,id'],
            'events.*.discipline_id' => ['required', 'integer', 'exists:disciplines,id'],
            'events.*.event_type_id' => ['required', 'integer', 'exists:event_types,id'],
            'events.*.selection_id' => ['required', 'integer', 'exists:selections,id'],
            'events.*.is_live' => ['required', 'boolean:'],
            'events.*.event_date' => ['required', 'date'],
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
            'is_live' => 'typ zdarzenia',
        ]);
        return $request->all();
    }

    private function sendSlipToEmail(Slip $slip): void
    {
        Mail::to(User::all())->send(new \App\Mail\SlipCreated($slip));
    }
}
