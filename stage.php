<!doctype html>
<html lang="fr">
    <head>
    <meta charset="utf-8">
    
    <title>Offres de stage</title>
    <link rel="stylesheet" href="style/style_entreprises.css">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  </head>
  <body>

    <center><img src="image/logo-lbp-header.png"></center>
     <hr>

    <center><h1>Bienvenue sur lebonplan </h1></center>

    <center><h2 class="text-centerb">Offres de stage</h2></center>

    <fieldset>
        <legend>
            <p><strong>Rechercher une offre de stage</strong></p>
        </legend>
        <form>
                <input type="text" placeholder="Titre, compétences, description, base de rénumération, nombre d'étudiants ayant postulé, date de l'offre" id="critere" onkeyup="rechercheroffre()"><br><br>
                <button type="submit">Rechercher</button>
        </form>
    </fieldset>

    <!-- Formulaire pour entrer les détails de l'offre -->
    <fieldset>
        <legend> 
            <p><strong>Créer une offre</strong></p>
        </legend>
        <section>
            <article>
                <form id="formAjouterOffre">
                    <label for="titre">Titre :
                    <input type="text" id="titre" required><br><br></label>
            
                    <label for="description">Description :
                    <input type="text" id="description" required><br><br></label>
            
                    <label for="competences">Compétences requises :
                    <input type="text" id="competences" required><br><br></label>
                    
                    <label for="entreprise">Entreprise :
                    <input type="text" id="entreprise" required><br><br></label>
            
                    <label for="basederemuneration">Base de rénumération :
                    <input type="text" id="basederemuneration" required><br><br></label>

                    <label for="nbetudiants">Nombre d'étudiants ayant postulé :
                    <input type="number" id="nbetudiants" required><br><br></label>
            
                    <label for="dateoffre">Date de publication de l'offre :
                    <input type="date" id="dateoffre" required><br><br></label>
            
                    <button type="submit">Ajouter l'offre</button>
                </form>
            </article>
    
        </section>
    
    </fieldset>

<h2><strong>Listes des offres créées</strong></h2>
<table border="1">
    <thead>
        <tr>
            <th>Titre</th>
            <th>Description</th>
            <th>Compétences</th>
            <th>Entreprise</th>
            <th>Base de rémunération</th>
            <th>Nombre d'étudiants ayant déjà postulé</th>
            <th>Dates de l'offre</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody id="maTable">
        
    </tbody>
</table>

<h2><strong>Répartition par compétences</strong></h2>
<table border="1">
    <thead>
        <tr>
            <th>Compétences</th>
            <th>Offres</th>
        </tr>
    </thead>
    <tbody id="compTable">
        <tr>
            <td>Réseau</td>
            <td>A</td>
        </tr>
        <tr>
            <td>Cybersécurité</td>
            <td>B</td>
        </tr>
        <tr> 
            <td>Développement web</td>
            <td>BA</td>
        </tr>
    </tbody>
</table>

<h2><strong>Répartition par durée de stage</strong></h2>
<table border="1">
    <thead>
        <tr>
            <th>Durée de stage</th>
            <th>Offres</th>
        </tr>
    </thead>
    <tbody id="DureeTable">

    </tbody>
</table>

<h2><strong>Top des offres sur la wish list</strong></h2>
<table border="1">
    <thead>
        <tr>
            <th>Top des offres</th>
            <th>Offres</th>
        </tr>
    </thead>
    <tbody id="wishTable">

    </tbody>
</table>

<script>
    let companyCount = 2;

    document.getElementById("formAjouterOffre").addEventListener("submit", function(event) {
        event.preventDefault(); 

        let titre = document.getElementById("titre").value;
        let description = document.getElementById("description").value;
        let competences = document.getElementById("competences").value;
        let entreprise = document.getElementById("entreprise").value;
        let basederemuneration = document.getElementById("basederemuneration").value;
        let nbetudiants = document.getElementById("nbetudiants").value;
        let dateoffre = document.getElementById("dateoffre").value;

        if (titre && description && competences && basederemuneration && nbetudiants &&  dateoffre) {
            let table = document.getElementById("maTable");
            let newRow = table.insertRow();
            let ratingGroup = "rating_" + companyCount;

            newRow.innerHTML = `
                <td>${titre}</td>
                <td>${description}</td>
                <td>${competences}</td>
                <td>${entreprise}</td>
                <td>${basederemuneration}</td>
                <td>${nbetudiants}</td>
                <td>${dateoffre}</td>
                <td>
                    <button onclick="modifieroffre(this)">Modifier</button>
                    <button onclick="supprimeroffre(this)">Supprimer</button>
                </td>
            `;
            companyCount++;
            document.getElementById("formAjouterOffre").reset();
        }
    });

    function supprimeroffre(button) {
        if (confirm("Voulez-vous vraiment supprimer cette entreprise ?")) {
            let row = button.parentNode.parentNode;
            row.parentNode.removeChild(row);
        }
    }

    function modifieroffre(button) {
        let row = button.parentNode.parentNode;
        let cells = row.getElementsByTagName("td");

        let titre = prompt("Modifier le titre:", cells[0].innerText);
        let description = prompt("Modifier la description:", cells[1].innerText);
        let competences = prompt("Modifier les compétences:", cells[2].innerText);
        let entreprise = prompt("Modifier le nom de l'entreprise:", cells[3].innerText);
        let basederemuneration = prompt("Modifier la base de rémunération:", cells[4].innerText);
        let nbetudiants = prompt("Modifier le nombre d'étudiants:", cells[5].innerText);
        let dateoffre = prompt("Modifier la date de publication:", cells[6].innerText);

        if (titre && description && competences && entreprise && basederemuneration && nbetudiants && dateoffre) {
            cells[0].innerText = titre;
            cells[1].innerText = description;
            cells[2].innerText = competences;
            cells[3].innerText = entreprise;
            cells[4].innerText = basederemuneration;
            cells[5].innerText = nbetudiants;
            cells[6].innerText = dateoffre;
        }
    }

    function rechercheroffre() {
        let input = document.getElementById("critere").value.toLowerCase();
        console.log(input); // Vérifiez si l'entrée est bien reçue
        let rows = document.getElementById("maTable").getElementsByTagName("tr");

        for (let row of rows) {
            if (row.querySelector('th')) continue;
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
   