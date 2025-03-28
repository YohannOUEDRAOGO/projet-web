<!doctype html>
<html lang="fr">
  <head>
    <meta charset="utf-8">
    <title>Compte pilote</title>
    <link rel="stylesheet" href="style/style_entreprises.css">

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
                <input type="text" placeholder="Email, nom, prenom" id="search" onkeyup="RechercherCompte()" required>
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
            
            <button type="submit">Créer</button>
        </form>
    </fieldset>
    

<script>
    let companyCount = 2;

    document.getElementById("formAjouterCompte").addEventListener("submit", function(event) {
        event.preventDefault(); 

        let email = document.getElementById("email").value;
        let nom = document.getElementById("nom").value;
        let prenom = document.getElementById("prenom").value;

        if (email && nom && prenom) {
            let table = document.getElementById("maTable");
            let newRow = table.insertRow();
            let ratingGroup = "rating_" + companyCount;

            newRow.innerHTML = 
                <td>${email}</td>`
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

        let email = prompt("Modifier l'Email':", cells[0].innerText);
        let nom = prompt("Modifier le nom:", cells[1].innerText);
        let prenom = prompt("Modifier le prénom:", cells[2].innerText);

        if (email && nom && prenom) {
            cells[0].innerText = email;
            cells[1].innerText = nom;
            cells[2].innerText = prenom;
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



 