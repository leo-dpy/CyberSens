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
├── robots.txt           # Règles d'indexation SEO
├── sitemap.xml          # Sitemap XML pour les moteurs de recherche
│
├── frontend/            # Assets, scripts et vues
│   ├── css/             # Styles CSS (thème Neon Glass)
│   ├── js/              # Logique JavaScript modulaire
│   └── templates/       # Templates HTML (chargés dynamiquement, ex: home, cours)
│
├── backend/             # Logique serveur et interfaces (PHP)
│   ├── admin/           # Interface d'administration sécurisée
│   │   ├── index.php    # Dashboard admin
│   │   ├── auth.php     # Contrôle d'accès basé sur les rôles
│   │   └── ...
│   └── api/             # API REST et logique métier
│       ├── db.php       # Configuration base de données
│       ├── security.php # Couche de sécurité (CORS, Limiting, Session)
│       └── ...
│
├── database/            # Scripts SQL
│   └── cybersens.sql    # Structure et données initiales
│
└── apache-config.conf   # Configuration serveur Web Apache
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

⚠️ **TOUS DROITS RÉSERVÉS (ALL RIGHTS RESERVED)**

Ce projet est sous **licence propriétaire exclusive**.
Il est **strictement interdit** de copier, modifier, distribuer ou vendre ce projet, en tout ou partie.

**Aucune reprise du projet (fork, hébergement public/privé, réutilisation du code) n'est autorisée** sans l'accord écrit explicite de l'auteur.

Consultez le fichier `LICENSE` pour les détails légaux complets.


