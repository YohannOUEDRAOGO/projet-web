# projet
1. Présentation du projet
Les étudiants effectuent leurs recherches de stage en entreprise en activant leurs réseaux personnels et professionnels (LinkedIn, anciennes promotions, etc.) et en postulant à des offres.

Afin de rendre cette dernière étape de recherche de stage plus facile et pratique, il serait nécessaire de disposer d'un site web qui regroupe différentes offres de stage, et qui permettra de stocker les données des entreprises ayant déjà pris un stagiaire, ou qui en recherchent un.

1.1 Déroulement

Le projet se déroule pratiquement tout le long du bloc. Des temps projets sont prévus régulièrement, ce qui vous permettra d'avancer progressivement votre projet à l'aide de vos nouvelles connaissances acquises à l'issue de chaque prosit. Les livrables des prosits seront directement des fonctionalités du projet

Ce projet est découpé en 4 temps :

Composition des groupes de projet, choix des méthodes de travail, définition des rôles, organisation/planification ;

Maquettage puis frontend (HTML/CSS/JS) 

Modélisation (MCD) et mise en place de la base de données

Développement du backend.

Le projet est dimensionné pour des groupes de 4.

1.2 Livrable

Le bloc termine sans surprise par une soutenance. Durant cette dernière, vous allez vous positionner comme le prestataire (Web4All) qui vient montrer à son client CESI (le jury) le résultat de sa commande.

La soutenance peut être composée d'une petite présentation de 5 minutes et surtout d'une démonstration technique. Le temps étant compté, le jury pourra vous guider par ses questions pour vérifier telle ou telle spécificité (fonctionnelle comme technique). La séquence se terminera hors contexte par des questions/réponses individuelles permettant d'évaluer votre implication personnelle dans le projet.

2. Cahier des charges du projet
La réalisation d'une application web pour les stages se trouve être un projet plein d'ambitions. Le site va permettre d'informatiser l'aide à la recherche de stages en regroupant toutes les offres de stage. Il permettra entre autres d'enregistrer les données des entreprises ayant déjà pris un stagiaire, ou qui en recherchent un.

Ceci facilitera l'orientation des nouveaux étudiants dans leurs recherches de stages.

Les offres de stage seront notamment enregistrées par compétences, ce qui permettra à l'étudiant de trouver un stage en rapport avec son profil. L'application doit fournir différentes interfaces à destination des différents profils d'utilisateurs.

Les profils d'utilisateurs sont l'administrateur, le pilote de promotion et l'étudiant. Parmi les fonctionnalités attendues sont la gestion des rôles, la gestion des entreprises, la gestion des offres de stage et la gestion des candidatures. Selon le profil d'utilisateur, ce dernier pourra accéder à certains services et pas d'autres. Seuls les administrateurs ont accès à l'ensemble des fonctionnalités proposées par la plateforme.

Ce cahier des charges laisse place à des interprétations, différentes options possibles et des champs de libertés. Vous devez analyser, faire ressortir les zones d'ombres, les options et autres incertitudes de manière à réfléchir à la meilleure ligne de conduite pour votre groupe et ainsi de proposer à votre client (pilote de promotion).

Outre les fonctionnalités techniques, votre site devra s'adapter au mieux en fonction de l'équipement de l'utilisateur (responsive) et respecter les bonnes pratiques de codage côté back-end comme front-end.

Par ailleurs il va sans dire que vous veillerez à la conformité légale de votre site, notamment que les mentions légales obligatoires soient présentes.

2.1 Spécifications fonctionnelles
Vous trouverez dans cette section les spécifications fonctionnelles du projet. Une matrice de gestion des rôles est disponible en Annexe. Le critère « data » représente les données à fournir ou que l'on peut fournir en entrée de procédure.

Gestion d'accès

Dans cette catégorie la fonctionnalité attendue est :

SFx 1 – Authentifier

Description : Cette fonctionnalité doit permettre à l'utilisateur de s'authentifier. En cas de réussite de la procédure, l'utilisateur disposera des permissions auxquelles correspondantes à son rôle (voir matrice)

Data : [email – mot de passe]

Gestion des entreprises

Dans cette catégorie les fonctionnalités attendues sont :

SFx 2 – Rechercher et afficher une entreprise

Description : Cette fonctionnalité doit permettre à l'utilisateur de rechercher la fiche d'une entreprise sur la base de plusieurs critères. Il sera possible de consulter les offres liées à l'entreprise et de visualiser les différentes appréciations (entreprises / stages).

Data : [nom - description - email et téléphone de contact – nombre de stagiaires ayant postulé à une offre de cette entreprise – moyenne des évaluations].

SFx 3 – Créer une entreprise

Description : Cette fonctionnalité doit permettre à l'utilisateur de créer la fiche d'une entreprise.

Data : [nom - description - email et téléphone de contact ].

SFx 4 – Modifier une entreprise

Description : Cette fonctionnalité doit permettre à l'utilisateur de modifier la fiche d'une entreprise.

Data : [nom - description - email et téléphone de contact ].

SFx 5 – Évaluer une entreprise

Description : Cette fonctionnalité doit permettre à l'utilisateur (voir matrice) d'évaluer une entreprise qui propose des stages.

Data : [évaluation].

SFx 6 – Supprimer une entreprise

Description : Cette fonctionnalité doit permettre à l'utilisateur de sortir une entreprise du système afin qu'elle ne soit plus proposée aux étudiants.

Gestion des offres de stage

Attention, vous devez réfléchir à la meilleure manière de gérer les compétences, il n'est pas nécessaire de pouvoir modifier la liste des compétences. Dans cette catégorie les fonctionnalités attendues sont :

SFx 8 – Rechercher et afficher une offre

Description : Cette fonctionnalité doit permettre à l'utilisateur de rechercher une offre sur la base de plusieurs critères.

Data : [entreprise - titre - description - compétences – base de rémunération – dates de l'offre - nombre d'étudiants ayant déjà postulé à cette offre].

SFx 9 – Créer une offre

Description : Cette fonctionnalité doit permettre à l'utilisateur de créer une offre et de la paramétrer.

Data : [compétences – - titre - description - entreprise – base de rémunération – dates de l'offre].

SFx 10 – Modifier une offre

Description : Cette fonctionnalité doit permettre à l'utilisateur de modifier une offre ainsi que ses paramètres.

Data : [compétences – - titre - description - entreprise – base de rémunération – dates de l'offre].

SFx 11 – Supprimer une offre

Description : Cette fonctionnalité doit permettre à l'utilisateur de retirer du système une offre.

SFx 12 – Consulter les statistiques des offres

Description : Dashboard donnant une vue globale des stages entrés en base

Data : [Répartition par compétence, par durée de stage, le top des offres mises en wish list]

Gestion des pilotes de promotions

Dans cette catégorie les fonctionnalités attendues sont :

SFx 13 – Rechercher et afficher un compte Pilote

Description : Cette fonctionnalité doit permettre à l'utilisateur de rechercher un compte Pilote.

Data : [nom – prénom].

SFx 14 – Créer un compte Pilote

Description : Cette fonctionnalité doit permettre à l'utilisateur de créer un compte Pilote.

Data : [nom – prénom].

SFx 15 – Modifier un compte Pilote

Description : Cette fonctionnalité doit permettre à l'utilisateur de créer un compte Pilote.

Data : [nom – prénom].

SFx 16 – Supprimer un compte Pilote

Description : Cette fonctionnalité doit permettre à l'utilisateur de supprimer un compte Pilote.

Gestion des étudiants

Dans cette catégorie les fonctionnalités attendues sont :

SFx 17 – Rechercher et afficher un compte Etudiant

Description : Cette fonctionnalité doit permettre à l'utilisateur de rechercher un compte Etudiant à partir de plusieurs critères et d'afficher ses informations, ainsi que l'état de la recherche de stage

Data : [nom – prénom - email].

SFx 18 – Créer un compte Etudiant

Description : Cette fonctionnalité doit permettre à l'utilisateur de créer un compte Etudiant.

Data : [nom – prénom – email].

SFx 19 – Modifier un compte Etudiant

Description : Cette fonctionnalité doit permettre à l'utilisateur de modifier un compte Etudiant.

Data : [nom – prénom – email].

SFx 20 – Supprimer un compte Etudiant

Description : Cette fonctionnalité doit permettre à l'utilisateur de supprimer un compte Etudiant.

SFx 21 – Consulter les statistiques d'un compte Etudiant

Description : Cette fonctionnalité doit permettre à l'utilisateur de suivre la recherche de stage d'un compte Etudiant.

Data : [nom – prénom – email].

Gestion des candidatures

Dans cette catégorie les fonctionnalités attendues sont :

SFx 22 – Ajouter une offre à la wish-list

Description : Cette fonctionnalité doit permettre à l'utilisateur d'ajouter l'offre à sa liste d'intérêts pour lui permettre de garder une trace des offres qu'il souhaite conserver.

SFx 23 – Retirer une offre à la wish-list

Description : Cette fonctionnalité doit permettre à l'utilisateur de retirer une offre présente dans sa liste d'intérêts.

SFx 24 – Afficher les offres ajoutées à la wish-list

Description : Cette fonctionnalité doit permettre à l'utilisateur de retirer une offre présente dans sa liste d'intérêts

SFx 25 – Postuler à une offre

Description : Cette fonctionnalité doit permettre à l'utilisateur de saisir une lettre de motivation (champ texte) et de téléverser un CV. 

Data : [date de candidature - cv – lettre de motivation]

SFx26 - Afficher les offres en cours de candidature

Description : Cette fonctionnalité permettre de voir les offres auxquelles l'étudiant a postulé

Data : [entreprise - offre – date - lettre de motivation]

Autres fonctionnalités

SFx 27 – Pagination

Chaque affichage de données pouvant recevoir de nombreux résultats (liste d'utilisateurs, d'entreprises, d'offres...) doit contenir une pagination.

Bonus : Accès mobile du site web

Une fois que l'application web est mise en place, il est possible de la transformer en application mobile en utilisant le PWA.

Ceci permettra à votre Web App d'être installée comme une application native (icone sur les écrans du mobile, navigation plein écran, navigation hors-ligne...).

2.2 Spécifications techniques
Vous trouverez ci-dessous les spécifications techniques à respecter.

STx 1 – Architecture

Architecture MVC obligatoire

STx 2 – Conformité du code

Chaque page HTML doit contenir une syntaxe précise constituée de balises sémantiques HTML5 (et éventuellement de balises spécifiques à un framework). Chaque page HTML générée doit être validée par le validateur W3C. Le code CSS doit être bien structuré et cohérent. Coté PHP, l'usage de la POO est obligatoire. Il sera apprécier de respecter les principales conventions PSR-12.

STx 3 – Contrôle des champs des formulaires

Les champs des formulaires devront être vérifiés/validés coté front ET back.

STx 4 – Interdiction d'utiliser les CMS

Pas d'utilisation de CMS (wordpress...).

STx 5 – Stack technique

Apache

HTML5/CSS3/JS : l'utilisation d'un préprocesseur CSS (LESS, Sass...) est possible, de même que des bibliothèque JS comme JQuery

PHP : Utilisation de PHP pour la partie back-end. Il est possible d'utiliser un framework type Laravel.

Base de données : base SQL au choix par exemple MySQL, PostgreSQL, MariaDB...

STx 6 – Moteur de template

Tout le site doit utiliser un moteur de template coté Backend, le code devra faire bon usage de ce moteur.

STx 7– Utiliser des clés étrangères

pour les relations du SGBD lorsque cela est possible.

STx 8- Vhost

Plusieurs vhost seront utilisés dans la configuration Apache : un vhost spécifique accueillera tout le contenu statique (images, css, js....)

STx 9– Responsive Design

Les pages du site (menus, texte, images...) doivent s'adapter à la taille de l'écran

STx 10 – Sécurité

Les informations de connexion doivent être dans des cookies. Aucune information sensible ne doit être stockée en clair que ce soit dans les cookies ou la base de données. Des mécanismes doivent être mis en place pour contrecarrer des tentatives d'attaques par injections SQL.
