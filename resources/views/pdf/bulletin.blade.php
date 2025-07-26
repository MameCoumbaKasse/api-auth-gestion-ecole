<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bulletin de {{ $eleve->user->prenom }} {{ $eleve->user->nom }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 13px;
            color: #333;
            margin: 40px;
        }

        h1, h2, h3 {
            text-align: center;
            margin: 0;
            padding: 0;
        }

        h1 {
            font-size: 22px;
            text-transform: uppercase;
        }

        h2 {
            font-size: 16px;
            margin-top: 5px;
        }

        .info {
            margin-top: 20px;
            margin-bottom: 15px;
        }

        .info p {
            margin: 3px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            background-color: #f2f2f2;
            text-transform: uppercase;
        }

        td, th {
            border: 1px solid #999;
            padding: 8px;
            font-size: 13px;
        }

        .footer {
            margin-top: 25px;
        }

        .footer p {
            margin: 6px 0;
        }

        .right {
            text-align: right;
        }

        .left {
            text-align: left;
        }

        .center {
            text-align: center;
        }

        .note-excellent {
            background-color: #d4edda;
        }

        .note-faible {
            background-color: #f8d7da;
        }
    </style>
</head>
<body>
    <h1>Bulletin Scolaire</h1>
    <h2>Période : {{ $periode }}</h2>

    <div class="info">
        <p><strong>Élève :</strong> {{ $eleve->user->prenom }} {{ strtoupper($eleve->user->nom) }}</p>
        <p><strong>Classe :</strong> {{ $eleve->classe->nom ?? '-' }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="left">Matière</th>
                <th>Note</th>
                <th>Coefficient</th>
                <th>Note x Coeff</th>
                <th>Enseignant</th>
            </tr>
        </thead>
        <tbody>
            @foreach($notes as $note)
                @php
                    $isExcellent = $note->note >= 16;
                    $isFaible = $note->note < 10;
                    $noteClass = $isExcellent ? 'note-excellent' : ($isFaible ? 'note-faible' : '');
                @endphp
                <tr class="{{ $noteClass }}">
                    <td class="left">{{ $note->matiere->nom }}</td>
                    <td>{{ number_format($note->note, 2) }}</td>
                    <td>{{ $note->matiere->coefficient }}</td>
                    <td>{{ number_format($note->note * $note->matiere->coefficient, 2) }}</td>
                    <td>{{ $note->matiere->enseignant->user->nom }} {{ $note->matiere->enseignant->user->prenom }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p><strong>Moyenne Générale :</strong> {{ number_format($moyenne, 2) }} / 20</p>
        <p><strong>Mention :</strong> {{ $mention }}</p>
        <p><strong>Rang :</strong> {{ $rang }}</p>
        <p><strong>Appréciation :</strong> {{ $appreciation }}</p>
    </div>

    <p class="right" style="margin-top: 40px;">Fait le {{ now()->format('d/m/Y') }}</p>

</body>
</html>