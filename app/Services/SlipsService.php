<?php

namespace App\Services;

use App\Models\Competitor;
use App\Models\EventType;
use App\Models\Selection;
use App\Models\Slip;
use App\Models\User;
use App\Repositories\SlipsRepository;
use Illuminate\Http\Request;
use Mail;

readonly class SlipsService
{
    public function __construct(private SlipsRepository $slipsRepository, private OpenaiService $openaiService){}
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
            'events.*.home_id' => 'gospodarz',
            'events.*.away_id' => 'gość',
            'events.*.event_date' => 'data meczu',
            'events.*.discipline_id' => 'dyscyplina',
            'events.*.event_type_id' => 'rodzaj zakładu',
            'events.*.selection_id' => 'typ',
            'events.*.is_live' => 'typ zdarzenia',
            'stake' => 'stawka',
            'odds' => 'kurs',
        ]);
        return $request->all();
    }

    private function sendSlipToEmail(Slip $slip): void
    {
        Mail::to(User::all())->send(new \App\Mail\SlipCreated($slip));
    }

    public function analyzeBetScreenshot(Request $request): array
    {
        $request->validate([
            'screenshot' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        ], [
            'required' => 'Pole :attribute jest wymagane.',
            'image' => 'Pole :attribute musi być zdjęciem',
            'mimes' => 'Zdjęcie musi mieć rozszerzenie jpg, png, jpeg, gif lub svg.',
            'max' => 'Zdjęcie może mieć maksymalnie 2MB.',
        ]);
        $data = $this->openaiService->parseScreenshot($request->file('screenshot'));
        return $this->formatResponse($data);
    }

    private function formatResponse(array $slipData): array
    {
        $responseData = ['stake' => $slipData['stake'] ?? 0, 'odds' => $slipData['odds'] ?? 0];
        foreach ($slipData['bets']  ?? [] as &$bet) {
            if (isset($bet['home'])) {
                $home = Competitor::query()->where('name', $bet['home'])->firstOrCreate(['name' => $bet['home']]);
                $bet['home'] = $home;
            }
            if (isset($bet['away'])) {
                $away = Competitor::query()->where('name', $bet['away'])->firstOrCreate(['name' => $bet['away']]);
                $bet['away'] = $away;
            }
            if (isset($bet['discipline'])) {
                $discipline = EventType::query()->where('name', $bet['discipline'])->firstOrCreate(['name' => $bet['discipline']]);
                $bet['discipline'] = $discipline;
            }
            if (isset($bet['event_type'])) {
                $eventType = EventType::query()->where('name', $bet['event_type'])->firstOrCreate(['name' => $bet['event_type']]);
                $bet['event_type'] = $eventType;
            }
            if (isset($bet['selection'])) {
                $selection = Selection::query()->where('name', $bet['selection'])->firstOrCreate(['name' => $bet['selection']]);
                $bet['selection'] = $selection;
            }

            $responseData['bets'][] = $bet;
        }
        return $responseData;
    }
}
