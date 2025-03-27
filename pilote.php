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
    <center><img src="image/logo-lbp-header.png"></center>
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
                <input type="text" placeholder="Titre, Compétences, Description" id="search" onkeyup="RechercherCompte()" required>
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
            <label for="nom"><r>Nom</r></label>
            <div>
                <input type="text" id="nom" name="nom" required><br><br>
            </div>
            <label for="prenom"><r>Prénom</r></label>
            <div>
                <input type="text" id="prenom" name="prenom" required><br><br>
            </div>
            
            <button type="submit">Créer</button>
        </form>
    </fieldset>
    


    <h1>Liste des Comptes</h1>
    <table border="2">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="maTable">
            <tr>
                <td>Dupont</td>
                <td>Jean</td>
                <td>
                    <button onclick="modifierCompte(this)">Modifier</button>
                    <button onclick="supprimerCompte(this)">Supprimer</button>
                </td>
            </tr>
            <tr>
                <td>Durand</td>
                <td>Marie</td>
                <td>
                    <button onclick="modifierCompte(this)">Modifier</button>
                    <button onclick="supprimerCompte(this)">Supprimer</button>
                </td>
            </tr>
            <tr>
                <td>Martin</td>
                <td>Paul</td>
                <td>
                    <button onclick="modifierCompte(this)">Modifier</button>
                    <button onclick="supprimerCompte(this)">Supprimer</button>
                </td>
            </tr>
        </tbody>
    </table>

<script>
    let companyCount = 2;

    document.getElementById("formAjouterCompte").addEventListener("submit", function(event) {
        event.preventDefault(); 

        let nom = document.getElementById("nom").value;
        let prenom = document.getElementById("prenom").value;

        if (nom && prenom) {
            let table = document.getElementById("maTable");
            let newRow = table.insertRow();
            let ratingGroup = "rating_" + companyCount;

            newRow.innerHTML = `
                <td>${nom}</td>
                <td>${prenom}</td>
                <td>
                    <button onclick="modifierCompte(this)">Modifier</button>
                    <button onclick="supprimerCompte(this)">Supprimer</button>
                </td>
            `;
            companyCount++;
            document.getElementById("formAjouterCompte").reset();
        }
    });

    function supprimerCompte(button) {
        if (confirm("Voulez-vous vraiment supprimer cette entreprise ?")) {
            let row = button.parentNode.parentNode;
            row.parentNode.removeChild(row);
        }
    }

    function modifierCompte(button) {
        let row = button.parentNode.parentNode;
        let cells = row.getElementsByTagName("td");

        let nom = prompt("Modifier le nom:", cells[0].innerText);
        let prenom = prompt("Modifier le prénom:", cells[1].innerText);

        if (nom && prenom) {
            cells[0].innerText = nom;
            cells[1].innerText = prenom;
        }
    }

    function rechercherCompte() {
        let input = document.getElementById("critere").value.toLowerCase();
        let rows = document.getElementById("maTable").getElementsByTagName("tr");

        for (let row of rows) {
            //let name = row.getElementsByTagName("td")[0].innerText.toLowerCase();
            //row.style.display = name.includes(input) ? "" : "none";
            let cells = row.getElementsByTagName("td");
            let match = false;

            for (let cell of cells) {
                if (cell && cell.innerText.toLowerCase().includes(input)) {
                        match = true;
                        break;
                }
            }
            row.style.display = match || input === "" ? "" : "none";
        }
    }
</script>


    

    <footer class="navbar footer">
        <hr>
        <em>2024 - Tous droits réservés - Web4All</em>
    </footer>
    
  </body>

  
</html>



 