<!DOCTYPE html>
<html>
<head>
    <title>Nouveau compte utilisateur</title>
</head>
<body>
    <h1>Bienvenue, {{ $user->nom }} {{ $user->prenom }} !</h1>
    <p>Votre compte a été créé avec succès.</p>
    <p>Voici les détails de votre compte :</p>
    <ul>
        <li><strong>Nom :</strong> {{ $user->nom }}</li>
        <li><strong>Prénom :</strong> {{ $user->prenom }}</li>
        <li><strong>Login :</strong> {{ $user->login }}</li>
    </ul>
   <p></p>
   <p>À jamais dans les entres des Enfers...</p>
</body>
</html>