<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nowy Typ Bukmacherski</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #0f172a; color: #f8fafc; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background-color: #1e293b; border-radius: 12px; overflow: hidden; border: 1px solid #334155; }
        .header { background-color: #2563eb; padding: 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 20px; color: #ffffff; }
        .content { padding: 30px; }
        .match-title { font-size: 24px; font-weight: bold; text-align: center; margin-bottom: 20px; color: #38bdf8; }
        .details-grid { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .details-grid td { padding: 12px; border-bottom: 1px solid #334155; }
        .label { color: #94a3b8; font-size: 13px; text-transform: uppercase; letter-spacing: 1px; }
        .value { color: #f8fafc; font-weight: 600; text-align: right; }
        .footer { background-color: #1e293b; padding: 15px; text-align: center; font-size: 12px; color: #64748b; }
        .badge { background: #0ea5e9; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Pojawił się nowy typ! 🚀</h1>
    </div>

    <div class="content">
        <div class="match-title">
            {{ $bet->home->name }} vs {{ $bet->away->name }}
        </div>

        <table class="details-grid">
            <tr>
                <td class="label">Dyscyplina</td>
                <td class="value">{{ $bet->discipline->name }}</td>
            </tr>
            <tr>
                <td class="label">Data i godzina</td>
                <td class="value">{{ \Carbon\Carbon::parse($bet->event_date)->format('d.m.Y H:i') }}</td>
            </tr>
            <tr>
                <td class="label">Rodzaj zakładu</td>
                <td class="value">{{ $bet->eventType->name }}</td>
            </tr>
            <tr>
                <td class="label">Twój typ</td>
                <td class="value"><span class="badge">{{ $bet->selection->name }}</span></td>
            </tr>
            <tr>
                <td class="label">Kurs</td>
                <td class="value" style="color: #fbbf24;">{{ number_format($bet->odds, 2) }}</td>
            </tr>
            <tr>
                <td class="label">Sugerowana stawka</td>
                <td class="value">{{ $bet->stake }} zł</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Wiadomość wygenerowana automatycznie przez Twoją Aplikację.
    </div>
</div>
</body>
</html>
