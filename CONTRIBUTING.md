# Guide de Contribution - TodoList

Prérequis

Avant de contribuer, assurez-vous d'avoir installé les outils suivants :

PHP 8.2 ou supérieur

Symfony 7.3

Composer (gestionnaire de dépendances)

PostgreSQL (base de données)

Un serveur local (WAMP pour Windows, MAMP pour macOS)

Comment contribuer
## 1. Fork du projet

Cliquez sur le bouton "Fork" en haut de cette page
Renseignez un titre et une description (ces informations apparaîtront sur votre profil GitHub)
Cliquez sur "Create fork" pour importer le dépôt dans vos projets

## 2. Configuration locale
Clonez votre fork localement
```shell
git clone https://github.com/VOTRE-USERNAME/Todo-Co.git
cd Todo-Co
```

## 3. Ajoutez le dépôt original comme remote
```shell
git remote add upstream https://github.com/LanchesThomas/Todo-Co.git
```

## 4. Installation du projet en local
Suivez les étapes d'installation du README :

## 5. Installation des dépendances
```shell
composer install
```

## 6. Configuration de la base de données
```shell
cp .env .env.local
```

## 7. Modifiez .env.local avec vos identifiants de base de données

## 8. Création et migration de la base de données
```shell
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

## 9. Chargement des données de test
```shell
php bin/console doctrine:fixtures:load
```

## 10. Lancement du serveur de développement
```shell
symfony server:start
```

## 11. Créer une branche de travail
Assurez-vous d'être sur la branche main et qu'elle soit à jour
```shell
git checkout main
git pull upstream main
```

## 12. Créez une nouvelle branche avec un nom descriptif
```shell
git checkout -b feature/amelioration-interface-todo
```
ou
```shell
git checkout -b fix/correction-suppression-tache
```

## 13. Effectuer vos modifications
Apportez vos modifications en respectant les standards de code Symfony.

## 14. Tester vos modifications
bash# Lancez les tests PHPUnit
```shell
php bin/phpunit
```

## 15. Vérifiez la couverture de code (disponible dans le dossier coverage/)
```shell
php bin/phpunit --coverage-html coverage/
```

##16. Commit et push
Ajoutez vos fichiers modifiés
```shell
git add .
```

## 17. Committez avec un message descriptif
```shell
git commit -m "feat: amélioration de l'interface utilisateur pour les tâches"
```

## 18. Poussez votre branche
```shell
git push origin feature/amelioration-interface-todo
```

## 19. Créer une Pull Request

Rendez-vous sur GitHub dans votre fork

Cliquez sur "Compare & pull request"


### Remplissez la description avec :

Une description claire des modifications apportées

Le contexte et la motivation du changement

Les tests effectués

Les captures d'écran si modifications visuelles



### Types de contributions
🐛 Corrections de bugs

Correction d'erreurs dans le code existant

Amélioration de la sécurité de l'authentification

Correction de problèmes de performance

✨ Nouvelles fonctionnalités

Amélioration des fonctionnalités Todo existantes

Optimisation de l'interface utilisateur

Nouvelles fonctionnalités d'administration


📚 Documentation

Amélioration de la documentation technique

Mise à jour des diagrammes UML (public/ressources/)

Documentation d'implémentation

Rapport d'audit

🧪 Tests

Ajout de tests unitaires et fonctionnels

Amélioration de la couverture de code

Tests d'intégration pour l'authentification

🔒 Sécurité

Amélioration de l'authentification et autorisation

Correction de failles de sécurité

Renforcement des contrôles d'accès

Standards de code

Framework Symfony

Respectez les conventions Symfony :

Utilisez les annotations/attributs Symfony appropriés

Suivez l'architecture MVC

Respectez la structure des dossiers Symfony

Utilisez les services et l'injection de dépendances