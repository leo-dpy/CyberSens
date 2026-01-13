# CyberSens

**Plateforme web de sensibilisation à la cybersécurité** avec un design futuriste "Deep Void & Neon Glass".

---

## Fonctionnalités

- **Authentification** - Inscription, connexion et gestion des profils utilisateurs
- **Cours interactifs** - Modules d'apprentissage avec contenu riche
- **Quiz dynamiques** - Questions à choix multiple avec feedback détaillé
- **Simulations de phishing** - Scénarios réalistes (email, SMS, web)
- **Gamification** - Système XP, niveaux, badges et certificats
- **Classements** - Leaderboard global et par groupe
- **Notifications** - Alertes en temps réel pour les accomplissements
- **Ressources** - Bibliothèque de contenus pédagogiques
- **Administration** - Panneau complet de gestion (CRUD cours, questions, utilisateurs)

---

## Design

Thème visuel **"Deep Void & Neon Glass"** :
- Fond sombre spatial (`#030305`)
- Accents néon cyan (`#00f3ff`) et rose (`#ff0055`)
- Effets glassmorphism et animations fluides
- Interface responsive et moderne

---

## Structure du projet

```
cybersens/
├── index.html           # Point d'entrée SPA
├── main.js              # Logique JavaScript principale
├── styles.css           # Styles globaux (thème Neon Glass)
├── install.php          # Script d'installation de la BDD
├── logout.php           # Déconnexion utilisateur
│
├── admin/               # Interface d'administration
│   ├── index.php        # Dashboard admin
│   ├── cours.php        # Gestion des cours
│   ├── questions.php    # Banque de questions
│   ├── users.php        # Gestion des utilisateurs
│   ├── auth.php         # Contrôle d'accès basé sur les rôles
│   └── admin-style.css  # Styles spécifiques admin
│
├── backend/             # API REST (PHP)
│   ├── db.php           # Configuration base de données
│   ├── login.php        # Authentification
│   ├── register.php     # Inscription
│   ├── courses.php      # API cours
│   ├── questions.php    # API questions
│   ├── progression.php  # Suivi de progression
│   ├── badges.php       # Gestion des badges
│   ├── phishing.php     # Scénarios de phishing
│   └── ...
│
├── database/            # Scripts SQL
│   └── cybersens.sql    # Structure et données initiales
│
└── templates/           # Templates HTML (chargés dynamiquement)
    ├── home.html        # Page d'accueil
    ├── profil.html      # Profil utilisateur
    ├── cours.html       # Liste des cours
    ├── quiz.html        # Interface de quiz
    ├── phishing.html    # Simulations de phishing
    ├── ressources.html  # Ressources pédagogiques
    └── leaderboard.html # Classement
```

---

## Installation

### Prérequis
- PHP 7.4+
- MySQL / MariaDB
- Serveur web (Apache, WAMP, XAMPP...)

### Étapes

1. **Cloner le dépôt**
   ```bash
   git clone https://github.com/leo-dpy/cybersens.git
   ```

2. **Configurer la base de données**
   - Créez une base de données MySQL nommée `cybersens`
   - Modifiez les identifiants dans `backend/db.php` :
     ```php
     $host = 'localhost';
     $dbname = 'cybersens';
     $username = 'root';
     $password = '';
     ```

3. **Installer la base de données**
   - Option A : Importez `database/cybersens.sql` via phpMyAdmin
   - Option B : Accédez à `install.php` depuis votre navigateur

4. **Déployer**
   - Placez le dossier dans votre répertoire web (ex: `htdocs/cybersens`)
   - Accédez à `http://localhost/cybersens/`

---

## Rôles utilisateurs

| Rôle | Permissions |
|------|-------------|
| `user` | Accès aux cours, quiz, profil |
| `creator` | + Création de cours et questions |
| `admin` | + Gestion des utilisateurs |
| `superadmin` | Accès total, gestion des rôles |

---

## Technologies

- **Frontend** : HTML5, CSS3 (custom), JavaScript vanilla
- **Backend** : PHP 7.4+, PDO
- **Base de données** : MySQL / MariaDB
- **Icônes** : Lucide Icons
- **Éditeur WYSIWYG** : Quill.js

---

## Licence

Ce projet est diffusé sous une **licence propriétaire**.

Toute copie, redistribution, publication ou modification du code est interdite sans autorisation écrite préalable du titulaire des droits.

Usage autorisé sans autorisation préalable : consultation et usage interne pour tests, démonstrations ou maquettes au sein de votre organisation uniquement.

Consultez le fichier `LICENSE` pour les conditions complètes.


