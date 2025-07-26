<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bulletin disponible</title>
</head>
<body>
    <p>Bonjour,</p>

    <p>Le bulletin scolaire de l'élève <strong>{{ $bulletin->eleve->user->nom }} {{ $bulletin->eleve->user->prenom }}</strong> 
    pour la période <strong>{{ $bulletin->periode }}</strong> est désormais disponible.</p>

    <p>Vous pouvez le consulter en vous connectant sur notre plateforme avec vos identifiants.</p>

    <p>Cordialement,<br>L’administration</p>
</body>
</html>