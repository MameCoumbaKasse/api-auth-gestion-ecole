<!DOCTYPE html>
<html>
<head>
    <title>Vos identifiant de connexion</title>
</head>
<body>
    <h1>Bienvenue à {{ $user->prenom }} {{ $user->nom }} !</h1>
    <p>Le compte d'élève de votre enfant a été créé avec succès sur notre plateforme de gestion d'école.</p>
    <p>Ses identifiants :</p>
    <ul>
        <li><strong>Nom d'utilisateur :</strong> {{ $user->login }}</li>
        <li><strong>Mot de passe :</strong> {{ $password }}</li>
        <!-- Ajoutez d'autres informations de l'utilisateur ici -->
    </ul>
   <p></p>
   <p>À jamais dans les entres des Enfers...</p>
</body>
</html>