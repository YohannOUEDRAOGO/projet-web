<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

// Vérification de session et récupération de l'utilisateur
require_once 'check_session.php';
verifySession();
if (!isset($_SESSION['user'])) {
    header('Location: authentification.php');
    exit();
}

$currentUser = $_SESSION['user'];
$role = $currentUser['role'];

// Vérification des droits - Seul l'admin peut gérer les pilotes
$canCreatePilote = ($role === 'admin');
$canEditPilote = ($role === 'admin');
$canDeletePilote = ($role === 'admin');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gestion";

function genererMotDePasseUnique($pdo) {
    $majuscules = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $minuscules = 'abcdefghijklmnopqrstuvwxyz';
    $chiffres = '0123456789';
    $speciaux = '!@#$%^&*()_+-=[]{}|;:,.<>?';
    
    $tentatives = 0;
    $maxTentatives = 100;
    
    do {
        $motDePasse = '';
        $motDePasse .= $majuscules[rand(0, strlen($majuscules) - 1)];
        $motDePasse .= $minuscules[rand(0, strlen($minuscules) - 1)];
        $motDePasse .= $chiffres[rand(0, strlen($chiffres) - 1)];
        $motDePasse .= $speciaux[rand(0, strlen($speciaux) - 1)];
        
        $tousCaracteres = $majuscules . $minuscules . $chiffres . $speciaux;
        while (strlen($motDePasse) < 12) {
            $motDePasse .= $tousCaracteres[rand(0, strlen($tousCaracteres) - 1)];
        }
        
        $motDePasse = str_shuffle($motDePasse);
        $motDePasseHash = password_hash($motDePasse, PASSWORD_BCRYPT);
        
        $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE password = ?");
        $stmt->execute([$motDePasseHash]);
        $existe = $stmt->rowCount() > 0;
        
        $tentatives++;
        if ($tentatives >= $maxTentatives) {
            throw new Exception("Impossible de générer un mot de passe unique après $maxTentatives tentatives");
        }
    } while ($existe);
    
    return ['plain' => $motDePasse, 'hash' => $motDePasseHash];
}

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajouter'])) {
    $nom = htmlspecialchars($_POST['nom']);
    $prenom = htmlspecialchars($_POST['prenom']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    if (!empty($nom) && !empty($prenom) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        if (!empty($_POST['id'])) { 
            // Mise à jour (sans changer le mot de passe)
            $stmt = $pdo->prepare("UPDATE utilisateurs SET nom=?, prenom=?, email=? WHERE id=? AND role='pilote'");
            $stmt->execute([$nom, $prenom, $email, $_POST['id']]);
        } else {
            // Vérifier si l'email existe déjà
            $check = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
            $check->execute([$email]);
            
            if ($check->rowCount() > 0) {
                echo "<script>alert('Cet email est déjà utilisé');</script>";
            } else {
                try {
                    // Génération d'un mot de passe unique
                    $passwordData = genererMotDePasseUnique($pdo);
                    
                    // Ajout dans la base de données avec le rôle pilote
                    $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, prenom, email, password, role) VALUES (?, ?, ?, ?, 'pilote')");
                    $stmt->execute([$nom, $prenom, $email, $passwordData['hash']]);
                    
                    // Envoi d'email avec PHPMailer (identique à votre code actuel)
                    $mail = new PHPMailer(true);
                    
                        $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com';
                        $mail->SMTPAuth = true;
                        $mail->Username = 'cryptosteph17@gmail.com'; // Remplacez par votre email
                        $mail->Password = 'zgybnrnzconvsjvd'; // Remplacez par votre mot de passe
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port = 587;
                
                        $mail->setFrom('no-reply@lebonplan.com', 'Le Bon Plan');
                        $mail->addAddress($email);
                        $mail->Subject = 'Création de compte pilote';
                        $mail->Body = "Bonjour $prenom $nom,\n\nVotre compte pilote a bien été créé.\nIdentifiants:\nEmail: $email\nMot de Passe: {$passwordData['plain']}\n\nCordialement,\nL'équipe pédagogique.";
                
                        $mail->send();
                    
                } catch (Exception $e) {
                    die("Erreur: " . $e->getMessage());
                }
            }
        }
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }
}

// Suppression (uniquement pour les pilotes)
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM utilisateurs WHERE id=? AND role='pilote'");
    $stmt->execute([$_GET['delete']]);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// Récupération des pilotes seulement
$pilotes = $pdo->query("SELECT * FROM utilisateurs WHERE role='pilote'")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Gestion des Pilotes</title>
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
                    <div class="user-avatar">
                        <?php echo substr($currentUser['prenom'], 0, 1) . substr($currentUser['nom'], 0, 1); ?>
                    </div>
                    <span class="user-name">
                        <?php echo htmlspecialchars($currentUser['prenom'] . ' ' . $currentUser['nom']); ?>
                        <small>(<?php echo htmlspecialchars($role); ?>)</small>
                    </span>
                    <span class="dropdown-icon">▼</span>
                </div>
                <div class="dropdown-menu" id="dropdownMenu">
                    <a href="profil.php" class="dropdown-item">Mon profil</a>
                    <?php if ($role === 'etudiant'): ?>
                        <a href="wishlist.php" class="dropdown-item">Wish-list</a>
                    <?php endif; ?>
                    <div class="divider"></div>
                    <a href="authentification.php" class="dropdown-item" id="logoutBtn">Déconnexion</a>
                </div>
            </div>
        </nav>
        <nav>
            <a href="candidature.php">Accueil</a> |
            <a href="entreprise.php">Gestion des entreprises</a> |
            <a href="stage.php">Gestion des offres de stage</a> |
            <?php if ($role === 'admin'): ?>
                <strong>Gestion des pilotes</strong>|
            <?php endif; ?>
            <?php if (in_array($role, ['admin', 'pilote'])): ?>
                <a href="etudiant.php">Gestion des étudiants</a> |
            <?php endif; ?>
        </nav>
    </header>

    <main>
        <section>
            <article>
                <h2>Rechercher un pilote</h2>
                <input type="text" placeholder="Email, nom ou Prénom" id="search" onkeyup="searchPilote()" required>
                
                <?php if ($canCreatePilote || $canEditPilote): ?>
                <h2>Ajouter/Modifier un pilote</h2>
                <form method="POST" id="piloteForm" <?php echo (!$canCreatePilote && !$canEditPilote) ? 'class="disabled-form"' : ''; ?>>
                    <input type="hidden" id="editId" name="id">
                    
                    <label for="nom">Nom
                        <input type="text" name="nom" id="nom" required>
                    </label>
                    
                    <label for="prenom">Prénom
                        <input type="text" name="prenom" id="prenom" required>
                    </label>
                    <label for="email">Email
                        <input type="email" name="email" id="email" required>
                    </label>
                    
                    <button type="submit" name="ajouter" <?php echo (!$canCreatePilote && !$canEditPilote) ? 'disabled' : ''; ?>>Enregistrer</button>
                </form>
                <?php endif; ?>
                
                <h2>Liste des pilotes</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Email</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="piloteTable">
                        <?php foreach ($pilotes as $pilote): ?>
                        <tr>
                            <td><?= htmlspecialchars($pilote['nom']) ?></td>
                            <td><?= htmlspecialchars($pilote['prenom']) ?></td>
                            <td><?= htmlspecialchars($pilote['email']) ?></td>
                            <td>
                                <?php if ($canEditPilote): ?>
                                <button class="edit-btn" onclick="editPilote(
                                    '<?= $pilote['id'] ?>',
                                    '<?= addslashes($pilote['nom']) ?>',
                                    '<?= addslashes($pilote['prenom']) ?>',
                                    '<?= addslashes($pilote['email']) ?>'
                                )">Modifier</button>
                                <?php endif; ?>
                                <?php if ($canDeletePilote): ?>
                                <a href="?delete=<?= $pilote['id'] ?>" onclick="return confirm('Supprimer ce pilote?')" class="delete-btn">Supprimer</a>
                                <?php endif; ?>
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

    <script>
        function toggleMenu() {
            document.getElementById('dropdownMenu').classList.toggle('show');
        }

        window.onclick = function(e) {
            if (!e.target.matches('.user-info *')) {
                document.getElementById('dropdownMenu').classList.remove('show');
            }
        }

        function editPilote(id, nom, prenom, email) {
            document.getElementById('editId').value = id;
            document.getElementById('nom').value = nom;
            document.getElementById('prenom').value = prenom;
            document.getElementById('email').value = email;
            window.scrollTo(0, 0);
        }

        function searchPilote() {
            const filter = document.getElementById('search').value.toLowerCase();
            document.querySelectorAll('#piloteTable tr').forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        }

        document.getElementById('logoutBtn').addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Déconnexion ?')) {
                window.location.href = 'authentification.php';
            }
        });
    </script>
</body>
</html>