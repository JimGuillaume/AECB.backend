# Guide d'installation — AECB (Attendance & Employee Construction Business)

Ce guide décrit l'installation complète du projet AECB sur un nouvel ordinateur Windows,
incluant le backend PHP et le frontend Vue.js.

---

## Prérequis

### 1. WampServer
- Télécharger WampServer : https://www.wampserver.com/
- Installer dans `C:\wamp64\` (chemin par défaut)
- WampServer inclut **PHP 8.x**, **Apache** et **MySQL 8.x**
- Vérifier que PHP 8.0 ou supérieur est sélectionné dans WampServer

### 2. Node.js
- Télécharger Node.js : https://nodejs.org/
- Installer la version **20.19.0 ou supérieure** (ou 22.12.0+)
- npm est inclus automatiquement avec Node.js
- Vérifier l'installation :
  ```
  node --version
  npm --version
  ```

### 3. Composer
- Télécharger Composer : https://getcomposer.org/download/
- Installer Composer (Windows Installer)
- Vérifier l'installation :
  ```
  composer --version
  ```

### 4. Git
- Télécharger Git : https://git-scm.com/
- Installer avec les options par défaut
- Vérifier l'installation :
  ```
  git --version
  ```

---

## Étape 1 — Récupérer les projets

### Option A — Cloner depuis GitHub (recommandé)

Ouvrir un terminal PowerShell et exécuter :

```powershell
# Backend — doit être placé dans le dossier www de WAMP
cd C:\wamp64\www
git clone https://github.com/<votre-compte>/AECB.backend.git AECB.backend

# Frontend — peut être placé n'importe où
cd C:\Users\<votre-utilisateur>\source\repos\JimGuillaume
git clone https://github.com/<votre-compte>/AECB.frontend.git AECB.frontend
```

### Option B — Copier manuellement

- Copier le dossier backend dans : `C:\wamp64\www\AECB.backend\`
- Copier le dossier frontend dans : `C:\Users\<votre-utilisateur>\source\repos\JimGuillaume\AECB.frontend\`

---

## Étape 2 — Configurer le Backend

### 2.1 Installer les dépendances PHP

```powershell
cd C:\wamp64\www\AECB.backend
composer install
```

> Si Composer n'est pas dans le PATH, utiliser le chemin complet : `C:\ProgramData\ComposerSetup\bin\composer.phar install`

### 2.2 Vérifier la configuration de la base de données

Ouvrir le fichier [src/Infrastructure/Persistence/DatabaseConnection.php](src/Infrastructure/Persistence/DatabaseConnection.php) et vérifier que les paramètres correspondent à votre installation WAMP :

```php
host:     localhost
port:     3306
database: aecb_attendance
username: root
password: (vide par défaut)
```

> Si votre MySQL a un mot de passe root différent, modifier le champ `password` dans ce fichier.

---

## Étape 3 — Configurer la base de données

### 3.1 Démarrer WampServer

- Lancer WampServer depuis le menu Démarrer ou le raccourci bureau
- Attendre que l'icône dans la barre des tâches devienne **verte**
- Les deux services doivent être actifs : **Apache** et **MySQL**

### 3.2 Créer la base de données

- Ouvrir phpMyAdmin : http://localhost/phpmyadmin
- Se connecter avec :
  - Utilisateur : `root`
  - Mot de passe : *(vide par défaut)*
- Cliquer sur **Bases de données** dans le menu du haut
- Créer une nouvelle base de données nommée : `aecb_attendance`
  - Interclassement : `utf8mb4_general_ci`
- Cliquer sur **Créer**

### 3.3 Importer le schéma SQL

- Dans phpMyAdmin, cliquer sur la base de données `aecb_attendance` dans le panneau de gauche
- Cliquer sur l'onglet **Importer**
- Cliquer sur **Choisir un fichier** et sélectionner :
  ```
  C:\wamp64\www\AECB.backend\database\aecb_attendance.sql
  ```
- Cliquer sur **Importer**
- Attendre la confirmation : *"L'importation s'est terminée avec succès"*

> Le fichier SQL contient les tables, les procédures stockées et les données de test.

---

## Étape 4 — Configurer le Frontend

### 4.1 Installer les dépendances Node.js

```powershell
cd C:\Users\<votre-utilisateur>\source\repos\JimGuillaume\AECB.frontend
npm install
```

> Cette étape peut prendre quelques minutes selon la connexion internet.

### 4.2 (Optionnel) Configurer l'URL de l'API

Par défaut, le frontend pointe vers `http://127.0.0.1:8000`.
Si le backend tourne sur un autre port, créer un fichier `.env` à la racine du frontend :

```
VITE_API_BASE_URL=http://127.0.0.1:8000
```

---

## Étape 5 — Lancer le projet

Les deux serveurs doivent tourner en même temps. Ouvrir **deux terminaux séparés**.

### Terminal 1 — Backend (serveur PHP)

```powershell
cd C:\wamp64\www\AECB.backend
php -S 127.0.0.1:8000 -t public
```

Vous devriez voir :
```
PHP 8.x.x Development Server (http://127.0.0.1:8000) started
```

### Terminal 2 — Frontend (serveur Vite)

```powershell
cd C:\Users\<votre-utilisateur>\source\repos\JimGuillaume\AECB.frontend
npm run dev
```

Vous devriez voir :
```
  VITE v8.x.x  ready in xxx ms
  ➜  Local:   http://localhost:5173/
```

### Accéder à l'application

Ouvrir un navigateur et aller sur : **http://localhost:5173**

---

## Comptes de test

Tous les comptes utilisateurs ont le mot de passe par défaut : **`Dev1234`**

| Rôle         | Description                              |
|--------------|------------------------------------------|
| `admin`      | Accès complet à toutes les fonctions     |
| `manager`    | Gestion des équipes et des travailleurs  |
| `team_leader`| Vue équipe et gestion des présences      |
| `worker`     | Consultation de ses propres données      |

> Les identifiants (emails) des comptes se trouvent dans la table `users` de phpMyAdmin.

---

## Résolution de problèmes

### L'icône WampServer est orange ou rouge
- Clic droit sur l'icône → **Démarrer tous les services**
- Vérifier qu'aucun autre programme n'utilise le port 80 (Skype, IIS, etc.)

### Erreur CORS lors des appels API
- Vérifier que le backend tourne bien sur `http://127.0.0.1:8000`
- Vérifier que le frontend tourne bien sur `http://localhost:5173`
- Ces adresses sont les seules autorisées par la configuration CORS du backend

### Erreur de connexion à la base de données
- Vérifier que WampServer est démarré (icône verte)
- Vérifier les identifiants dans [src/Infrastructure/Persistence/DatabaseConnection.php](src/Infrastructure/Persistence/DatabaseConnection.php)
- Vérifier que la base de données `aecb_attendance` existe dans phpMyAdmin

### `composer` ou `php` non reconnu dans le terminal
- Redémarrer le terminal après l'installation de WampServer/Composer
- Vérifier que PHP est dans le PATH système :
  - Panneau de configuration → Système → Variables d'environnement
  - Ajouter `C:\wamp64\bin\php\php8.x.x\` à la variable `Path`

### `npm install` échoue
- Vérifier la version de Node.js : `node --version` (doit être ≥ 20.19.0)
- Supprimer le dossier `node_modules` et le fichier `package-lock.json`, puis relancer `npm install`

---

## Résumé des ports utilisés

| Service         | Adresse                        |
|-----------------|-------------------------------|
| Frontend (Vite) | http://localhost:5173          |
| Backend (PHP)   | http://127.0.0.1:8000         |
| MySQL           | localhost:3306                 |
| phpMyAdmin      | http://localhost/phpmyadmin    |
