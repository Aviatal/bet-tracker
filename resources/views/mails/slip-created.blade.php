<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nowy Kupon Bukmacherski</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #0f172a; color: #f8fafc; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background-color: #1e293b; border-radius: 12px; overflow: hidden; border: 1px solid #334155; }
        .header { background-color: #10b981; padding: 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 20px; color: #ffffff; }
        .content { padding: 25px; }

        /* Styl dla pojedynczego zdarzenia */
        .event-card { background-color: #0f172a; border-radius: 8px; padding: 15px; margin-bottom: 15px; border-left: 4px solid #10b981; }
        .match-title { font-size: 18px; font-weight: bold; margin-bottom: 10px; color: #38bdf8; }

        .details-grid { width: 100%; border-collapse: collapse; }
        .details-grid td { padding: 8px 0; border-bottom: 1px solid #334155; }
        .details-grid tr:last-child td { border-bottom: none; }

        .label { color: #94a3b8; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; }
        .value { color: #f8fafc; font-weight: 600; text-align: right; font-size: 14px; }

        /* Sekcja finansowa */
        .summary { background-color: #1e293b; border: 2px solid #334155; border-radius: 8px; padding: 20px; margin-top: 20px; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .summary-label { color: #94a3b8; font-weight: bold; }
        .summary-value { font-size: 18px; font-weight: 800; }

        .footer { background-color: #1e293b; padding: 15px; text-align: center; font-size: 12px; color: #64748b; }
        .badge { background: #0ea5e9; padding: 2px 8px; border-radius: 4px; font-size: 11px; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>{{ count($slip->bets) > 1 ? 'Nowy kupon AKO! 🔥' : 'Nowy typ (Single)! 🚀' }}</h1>
    </div>

    <div class="content">
        @foreach($slip->bets as $bet)
            <div class="event-card">
                <div class="match-title">
                    {{ $bet->home->name }} vs {{ $bet->away->name }}
                </div>

                <table class="details-grid">
                    <tr>
                        <td class="label">Dyscyplina</td>
                        <td class="value">{{ $bet->discipline->name }}</td>
                    </tr>
                    <tr>
                        <td class="label">Data</td>
                        <td class="value">{{ \Carbon\Carbon::parse($bet->event_date)->format('d.m.Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td class="label">Zakład</td>
                        <td class="value">{{ $bet->eventType->name }}</td>
                    </tr>
                    <tr>
                        <td class="label">Typ</td>
                        <td class="value"><span class="badge">{{ $bet->selection->name }}</span></td>
                    </tr>
                </table>
            </div>
        @endforeach

        <div class="summary">
            <table style="width: 100%;">
                <tr>
                    <td class="summary-label">Kurs całkowity</td>
                    <td class="summary-value" style="color: #fbbf24; text-align: right;">
                        {{ number_format($slip->odds, 2) }}
                    </td>
                </tr>
                <tr>
                    <td class="summary-label">Stawka</td>
                    <td class="summary-value" style="color: #ffffff; text-align: right;">
                        {{ number_format($slip->stake, 2) }} zł
                    </td>
                </tr>
                <tr style="border-top: 2px solid #334155;">
                    <td class="summary-label" style="padding-top: 10px; color: #10b981;">Ew. wygrana</td>
                    <td class="summary-value" style="padding-top: 10px; color: #10b981; text-align: right;">
                        {{ number_format($slip->odds * $slip->stake, 2) }} zł
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="footer">
        Wiadomość wygenerowana automatycznie przez Bet Tracker.
    </div>
</div>
</body>
</html>
