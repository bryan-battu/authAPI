# API d'authentification

Cette API d'authentification développée avec CodeIgniter 4 permet la gestion des comptes utilisateurs ainsi que l'authentification sécurisée via JWT (JSON Web Tokens). Elle dispose des fonctionnalités suivantes :

- Création d'un compte utilisateur (un compte peut être ouvert ou fermé et administrateur ou simple utilisateur)
- Récupération d'un compte utilisateur
- Modification de l'utilisateur
- Création d'un token de connection
- Rafraîchissement du token d'authentification à la demande
- Validation d'un access token

L'API est protégée contre les attaques de force brute grâce à une gestion appropriée des tentatives de connexion infructueuses.

## Configuration

Modifier le fichier `.env` pour configurer le secret JWT.
Suivre les instructions présentes dans la section JWT du fichier `.env`

Pour mettre en place la base de données et démarrer l'application, veuillez exécuter les commandes suivantes :

1. Installez les dépendances PHP en exécutant la commande suivante à la racine du projet :
   ```bash
   composer install
   ```
   
2. Ensuite, construisez et démarrez les conteneurs Docker avec :
   ```bash
   docker-compose up -d
   ```
   
3. Exécutez la migration pour créer les tables de base de données :
   ```bash
   php spark migrate
    ```
   
4. Lancer l'application avec :
   ```bash
   php spark serve
   ```
   

