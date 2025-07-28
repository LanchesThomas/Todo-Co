ToDoList - OpenClassRooms - Projet 8 - Améliorez une application existante de ToDo & Co
========

[![Codacy Badge](https://app.codacy.com/project/badge/Grade/a002ec63bce2413bbf986c48c9722866)](https://app.codacy.com/gh/LanchesThomas/Todo-Co/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)

### À propos

Bonjour et bienvenue sur le dépôt de mon travail, qui traite du huitième projet d'OpenClassrooms, intitulé **Améliorez une application existante de ToDo & Co** ! Vous trouverez, ci-après, la procédure d'installation pour prendre en main le code du projet.

Vous trouverez, dans le dossier `public/ressources`, les **diagrammes UML**, le **rapport d'audit**, ainsi que la **documentation d'implémentation de l'authentification**.

Vous trouverez, dans le dossier `coverage`, les fichiers de **rapport de tests**


## Remarque

Pour pouvoir installer ce projet, le gestionnaire de dépendance **Composer** doit être présent sur votre machine, un serveur local sous **PHP 8.2**, ainsi que le framework **Symfony** en version **7.3**. Si vous ne disposez pas de ces outils, vous pourrez les télécharger et les installer, en suivant ces liens :
- Télécharger [Composer](https://getcomposer.org/)
- Télécharger [Symfony](https://symfony.com/download)
- Télécharger [Wamp](https://www.wampserver.com/) (Windows)
- Télécharger [Mamp](https://www.wampserver.com/) (Mamp)

## Installation

1. À l'aide d'un terminal, créez un dossier à l'emplacement souhaité pour l'installation du projet. Lancez ensuite la commande suivante :

```shell
git clone https://github.com/LanchesThomas/Todo-Co.git
```


2. Lancez cette commande pour vous rendre dans le dossier adequat :

```shell
cd .\projet8-TodoList\
```


3. À la racine de ce répertoire, lancez la commande suivante pour installer les dépendances Composer :

```shell
composer install
```


4. Une fois l'installation des dépendances terminée, vous devez maintenant dupliquer le fichier `.env` situé à la racine du projet, puis renommer le nouveau fichier en `.env.local`, pour vous connecter à votre base de données. À la ligne 9, remplacez les identifiants de connexion par vos identifiants de base de données locale :

```shell
DATABASE_URL="postgresql://app:!ChangeMe!@127.0.0.1:5432/todo?serverVersion=16&charset=utf8"
```


5. Après avoir modifié le fichier `.env.local` avec vos informations de connexion, lancez cette commande pour créer et migrer la base de données :

```shell
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

6. Si tout s'est correctement déroulé, une nouvelle base de données `todo` est apparu parmi les tables de votre serveur local. Lancez ensuite la commande suivante pour générer un jeu de données initial, s'appuyant sur les fixtures :

```shell
php bin/console doctrine:fixtures:load
```


7. À ce stade, un jeu de données devrait avoir été créé.


8. Via le terminal, lancez la commande suivante pour démarrer l'application Symfony :

```zsh
symfony server:start
```
9. Pour pouvoir tester les fonctionnalités du site, veuillez utiliser les identifiants par défaut :
- Admin
	- ID : admin
	- MDP : 123456
- User
	- ID : user1
	- MDP : 123456
    
---

## Contribution

1. Pour contribuer au projet, il vous suffit de cliquer sur le bouton "**fork**", en haut de cette page.


2. Un titre et une description vous seront demandées. Ces informations apparaîtront telles que vous les avez remplies sur votre compte GitHub. Vous pouvez les modifier par la suite.


3. Cliquez ensuite sur le bouton "**Create fork**" pour importer le dépôt parmi vos projets sur GitHub.


4. Une fois le projet ToDo & Co importé sur votre GitHub, vous êtes libre d'y apporter des modifications. Pour ce faire, clônez simplement le projet en local et commencez les modifications sur la branche `main`, ou une branche dédiée.


### Merci pour votre attention !