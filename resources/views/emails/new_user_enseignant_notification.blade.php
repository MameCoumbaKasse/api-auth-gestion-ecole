<!DOCTYPE html>
<html>
<head>
    <title>Vos identifiants de connexion</title>
</head>
<body>
    <h1>Bienvenue, {{ $user->prenom }} {{ $user->nom }} !</h1>
    <p>Votre compte en tant qu'enseignant a été créé avec succès sur notre plateforme de gestion d'école.</p>
    <p>Voici vos identifiants :</p>
    <ul>
        <li><strong>Nom d'utilisateur :</strong> {{ $user->login }}</li>
        <li><strong>Mot de passe :</strong> {{ $password }}</li>
        <!-- Ajoutez d'autres informations de l'utilisateur ici -->
    </ul>
   <p></p>
   <p>À jamais dans les entres des Enfers...</p>
</body>
</html>