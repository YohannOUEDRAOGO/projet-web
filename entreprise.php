<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Gestion des Entreprises</title>
        <link href="style/style_entreprises.css" rel="stylesheet">
    </head>
    <body>
        <header>
            <nav class="navbar">
            <center>
                    <img src="image/logo-lbp-header.png" alt="Trouve ton stage en un click avec Lebonplan">
            </center>
        <div class="user-menu" id="userMenu">
            <div class="user-info" onclick="toggleMenu()">
                <div class="user-avatar">YR</div>
                <span class="user-name">Yohann Romarick</span>
                <span class="dropdown-icon">▼</span>
            </div>
            <div class="dropdown-menu" id="dropdownMenu">
                <a href="#" class="dropdown-item">Mon profil</a>
                <a href="#" class="dropdown-item">Wish-list</a>
                <div class="divider"></div>
                <a href="#" class="dropdown-item" id="logoutBtn">Déconnexion</a>
            </div>
        </div>
    </nav>
            <nav>
                <a href="">Accueil</a>&nbsp; |
                <a href="">Wish list</a>&nbsp; |
                Gestion des entreprises&nbsp; |
                <a href="stage.html">Gestion des offres de stage</a>&nbsp; |
                <a href="pilote.html">Gestion des pilotes de promotions</a>&nbsp; |
                <a href="etudiant.php">Gestion des étudiants</a>&nbsp; |
                <a href="">Gestion des candidatures</a>&nbsp; |
            </nav>

        </header>
        <main>
            <section>
                <article>
                    <h2>Rechercher une entreprise</h2>
                    <input type="text" placeholder="Nom de l'entreprise, Compétences, Description" size="40" id="search" onkeyup="searchCompany()" required>
                    
                    <h2>Créer une entreprise</h2>
                    <form action="Ajouter une entreprise" id="addCompanyForm">
                        <label for="Nom">Nom de l'entreprise
                            <input type="text" name="Nom" id="name" required>
                        </label>
                        
                        <label for="Description de l'entreprise">Description de l'entreprise
                            <input type="text" name="Description" id="description" required>
                        </label>
                        <label for="Lien vers l'entreprise">URL de l'entreprise
                            <input type="text" name="url" id="url" required>
                        </label>
                        
                        <label for="Email">Email
                            <input type="email" name="email" id="email" required>
                        </label>
                        
                        <label for="téléphone de contact">Téléphone
                            <input type="number" name="telephone" id="phone" required>
                        </label>
                        

                        
                        <button type="submit">Créer</button>
                    </form>
                    <br>
                    
                    <h2>Listes des entreprises</h2>
                    <table border="1">
                        <thead>
                            <tr>
                                <th>Nom de l'entreprise</th>
                                <th>Description</th>
                                <th>Email</th>
                                <th>Téléphone</th>
                                <th>Nombre de candidatures</th>
                                <th>Actions</th>
                                <th>Evaluation</th>
                            </tr>
                        </thead>
                        <tbody id="companyTable">
                        </tbody>
                    </table>
                </article>
                <script>
                    let companyCount = 2;

                    document.getElementById("addCompanyForm").addEventListener("submit", function(event) {
                        event.preventDefault(); 

                        let name = document.getElementById("name").value;
                        let description = document.getElementById("description").value;
                        let email = document.getElementById("email").value;
                        let phone = document.getElementById("phone").value;
                        let url = document.getElementById("url").value;

                        if (name && description && email && phone) {
                            let table = document.getElementById("companyTable");
                            let newRow = table.insertRow();
                            let ratingGroup = "rating_" + companyCount;

                            newRow.innerHTML = `
                                <td><a href="${url}" target="_blank">${name}</a></td>
                                <td>${description}</td>
                                <td>${email}</td>
                                <td>${phone}</td>
                                <td>0</td>
                                <td>
                                    <button class="edit-btn" onclick="editCompany(this)">Modifier</button>
                                    <button class="delete-btn" onclick="deleteCompany(this)">Supprimer</button>
                                </td>
                                <td>
                                    <div class="rating">
                                        <input type="radio" id="star5_${companyCount}" name="${ratingGroup}" value="5" />
                                        <label for="star5_${companyCount}" title="5 étoiles">&#9733;</label>
                                        
                                        <input type="radio" id="star4_${companyCount}" name="${ratingGroup}" value="4" />
                                        <label for="star4_${companyCount}" title="4 étoiles">&#9733;</label>
                                        
                                        <input type="radio" id="star3_${companyCount}" name="${ratingGroup}" value="3" />
                                        <label for="star3_${companyCount}" title="3 étoiles">&#9733;</label>
                                        
                                        <input type="radio" id="star2_${companyCount}" name="${ratingGroup}" value="2" />
                                        <label for="star2_${companyCount}" title="2 étoiles">&#9733;</label>
                                        
                                        <input type="radio" id="star1_${companyCount}" name="${ratingGroup}" value="1" />
                                        <label for="star1_${companyCount}" title="1 étoile">&#9733;</label>
                                    </div>
                                </td>
                            `;
                            companyCount++;
                            document.getElementById("addCompanyForm").reset();
                        }
                    });

                    function deleteCompany(button) {
                        if (confirm("Voulez-vous vraiment supprimer cette entreprise ?")) {
                            let row = button.parentNode.parentNode;
                            row.parentNode.removeChild(row);
                        }
                    }

                    function editCompany(button) {
                        let row = button.parentNode.parentNode;
                        let cells = row.getElementsByTagName("td");

                        let name = prompt("Modifier le nom:", cells[0].innerText);
                        let description = prompt("Modifier la description:", cells[1].innerText);
                        let email = prompt("Modifier l'email:", cells[2].innerText);
                        let phone = prompt("Modifier le téléphone:", cells[3].innerText);

                        if (name && description && email && phone) {
                            cells[0].innerText = name;
                            cells[1].innerText = description;
                            cells[2].innerText = email;
                            cells[3].innerText = phone;
                        }
                        
                    }
                    function toggleMenu() {
                    document.getElementById('dropdownMenu').classList.toggle('show');
                    }
                    window.onclick = function(event) {
                    if (!event.target.matches('.user-info') && !event.target.closest('.user-info')) {
                    const dropdown = document.getElementById('dropdownMenu');
                   if (dropdown.classList.contains('show')) {
                   dropdown.classList.remove('show');
        }
    }
}

document.getElementById('logoutBtn').addEventListener('click', function(e) {
    e.preventDefault();
    // Ajoutez ici votre logique de déconnexion
    alert('Déconnexion effectuée');

});
                    function searchCompany() {
                        let input = document.getElementById("search").value.toLowerCase();
                        let rows = document.getElementById("companyTable").getElementsByTagName("tr");
                        for (let row of rows) {
                            if (row.querySelector('th')) continue;
                             let cells = row.getElementsByTagName("td");
                             let matchFound = false;
                             for (let cell of cells) {
                                  if (cell.innerText.toLowerCase().includes(input)) {
                                    matchFound = true;
                                    break;
            }
        }
        row.style.display = matchFound ? "" : "none";
    }
}
                </script>
            </section>
        </main>
        <footer></footer>
    </body>
</html>