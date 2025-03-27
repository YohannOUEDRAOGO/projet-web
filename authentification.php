<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - LEBONPLAN</title>
    <link href="style/style_authentification.css" rel="stylesheet"/>
    
</head>
<body>

    <header>
        <img src="image/logo-lbp-header.png" alt="Lebonplan - Le meilleur site de recherche de stage" class="logo">
    </header>

    <div class="form-container">
        <h1>Bienvenue sur Lebonplan</h1>
        <p>Ne perdez plus de temps à chercher un stage : avec Lebonplan, accédez aux meilleures offres rapidement et efficacement !</p>

        <h2>Identification</h2>
        <form>
            <label for="email">Email</label>
            <input name="email" type="email" placeholder="Entrez votre email" required >

            <label for="password">Mot de passe</label>
            <input name="password" type="password" placeholder="Mot de passe (8 caractères minimum)" minlength="8" required>

            <button type="submit">Je me connecte</button>
        </form>

        <p>En cas de problème, veuillez contacter  
            <a href="mailto:Yohannodg@gmail.com">l'administrateur</a>
        </p>
    </div>

</body>
</html>
