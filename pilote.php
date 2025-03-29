<!doctype html>
<html lang="fr">
  <head>
    <meta charset="utf-8">
    <title>Compte pilote</title>
    <link rel="stylesheet" href="assets/style.css">

    <style>
        .rating {
                display: flex;
                flex-direction: row-reverse;
                justify-content: center;
                margin: 20px 0;
            }
            .rating input {
                display: none;
            }
            .rating label {
                font-size: 40px;
                color: #ddd;
                cursor: pointer;
                transition: color 0.2s;
            }
            .rating input:checked ~ label,
            .rating label:hover,
            .rating label:hover ~ label {
                color: gold;
            }
    </style>
  </head>
  <body>
    <center><img src="images/logo-lbp-header.png"></center>
    <hr>

    <center><h1>Bienvenue sur lebonplan </h1></center>

    <center><h3>Comptes des pilotes de formation</h3></center>

    <!-- Formulaire pour Rechercher un compte Pilote -->
    <fieldset>
        <legend>
            <p>Recherche un compte pilote</p>
        </legend>
        <form action="rechercher_Offre.php" method="post">
            <div>
                <input type="text" placeholder="Email, nom, prénom" id="search" onkeyup="RechercherCompte()" required>
            </div>
            <button type="submit">Rechercher</button>
        </form>
    </fieldset>
    
    
    <!-- Formulaire pour Créer un compte Pilote -->
    <fieldset>
        <legend>
            <p>Créer un compte pilote</p>
        </legend>

        <form id="formAjouterCompte">
            <label for="email"><r>Email</r></label>
            <div>
                <input type="text" id="email" name="email" required><br><br>
            </div>
            <label for="nom"><r>Nom</r></label>
            <div>
                <input type="text" id="nom" name="nom" required><br><br>
            </div>
            <label for="prenom"><r>Prénom</r></label>
            <div>
                <input type="text" id="prenom" name="prenom" required><br><br>
            </div>
        </nav>
        <nav>
            <a href="">Accueil</a> |
            <a href="entreprise.php">Gestion des entreprises</a> |
            <a href="stage.html">Gestion des offres de stage</a> |
            <strong>Gestion des pilotes |</strong>
            <a href="etudiant.php">Gestion des étudiants</a> |
            <a href="">Gestion des candidatures</a>
        </nav>
    </header>

    <main>
        <section>
            <article>
                <h2>Rechercher un pilote</h2>
                <input type="text" placeholder="Nom ou Prénom" id="search" onkeyup="searchPilote()" required>
                
                <h2>Ajouter/Modifier un pilote</h2>
                <form method="POST" id="piloteForm">
                    <input type="hidden" id="editId" name="id">
                    
                    <label for="nom">Nom
                        <input type="text" name="nom" id="nom" required>
                    </label>
                    
                    <label for="prenom">Prénom
                        <input type="text" name="prenom" id="prenom" required>
                    </label>
                    
                    <button type="submit" name="ajouter">Enregistrer</button>
                </form>
                
                <h2>Liste des pilotes</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="piloteTable">
                        <?php foreach ($pilotes as $pilote): ?>
                        <tr>
                            <td><?= htmlspecialchars($pilote['nom']) ?></td>
                            <td><?= htmlspecialchars($pilote['prenom']) ?></td>
                            <td>
                                <button class="edit-btn" onclick="editPilote(
                                    '<?= $pilote['id'] ?>',
                                    '<?= addslashes($pilote['nom']) ?>',
                                    '<?= addslashes($pilote['prenom']) ?>'
                                )">Modifier</button>
                                <a href="?delete=<?= $pilote['id'] ?>" onclick="return confirm('Supprimer ce pilote?')" class="delete-btn">Supprimer</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </article>
        </section>
    </main>

    <footer class="navbar footer">
        <hr>
        <em>2024 - Tous droits réservés - Web4All</em>
    </footer>
    
  </body>

  
</html>



 