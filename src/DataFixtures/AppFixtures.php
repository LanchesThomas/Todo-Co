<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Task;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    /**
     * @codeCoverageIgnore
     */
    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * @codeCoverageIgnore
     */
    public function load(ObjectManager $manager): void
    {
        // 1. Création des utilisateurs
        $users = [];

        // Admin
        $admin = new User();
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setUsername('admin');
        $admin->setEmail('admin@mail.com');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, '123456'));
        $manager->persist($admin);
        $users[] = $admin;

        // Utilisateur anonyme
        $anonymous = new User();
        $anonymous->setRoles(['ROLE_USER']);
        $anonymous->setUsername('anonymous');
        $anonymous->setEmail('anonymous@mail.com');
        $anonymous->setPassword($this->passwordHasher->hashPassword($anonymous, '123456'));
        $manager->persist($anonymous);
        $users[] = $anonymous;

        // 3 utilisateurs supplémentaires
        for ($i = 1; $i <= 3; $i++) {
            $user = new User();
            $user->setRoles(['ROLE_USER']);
            $user->setUsername('user' . $i);
            $user->setEmail('user' . $i . '@mail.com');
            $user->setPassword($this->passwordHasher->hashPassword($user, '123456'));
            $manager->persist($user);
            $users[] = $user;
        }

        // Flush pour insérer les utilisateurs et avoir leurs IDs
        $manager->flush();

        // 2. Création des tâches
        $tasksTitle = [
            'Planification vacances',
            'Appeler le boulanger',
            'Vaccin chien',
            'Préparer présentation',
            '08/09 Réunion équipe',
            'Mise à jour site',
            'Liste de films à voir',
            'Cours de piano',
            'Envoyer factures',
            'Cours de yoga',
            '17/11 Audit interne'
        ];

        $tasksContent = [
            'Destination : Corse <br> Dates : 12/08 – 19/08 <br> Réserver : vol, voiture de location, hébergement',
            'Boulangerie Dupont <br> Tel : 01 23 45 67 89 <br> Commander : 10 baguettes, 2 pains aux raisins',
            'Clinique vétérinaire du Parc <br> RDV : 05/10 à 14h30 <br> Prévoir carnet de santé',
            '- Créer diapos intro <br> - Intégrer graphiques ventes <br> - Rédiger conclusion <br> - Répéter oral',
            'Salle 302, Bât. A <br> Participants : Julien, Sophie, Karim <br> Ordre du jour : objectifs Q4',
            '- Mettre à jour page “À propos” <br> - Corriger bug formulaire contact <br> - Optimiser images',
            '1. Inception <br> 2. Parasite <br> 3. Le Mans ’66 <br> 4. Minari <br> 5. Dune',
            'Pratiquer : <br> • Clair de lune (Beethoven) <br> • Gymnopédie n°1 (Satie) <br> • Prelude in C (Bach)',
            'Client A : 1234€ <br> Client B : 875€ <br> Client C : 2 450€ <br> Date limite : 30/07',
            'Lundi & Jeudi – 18h00 @ Studio Om <br> Apporter tapis & bouteille d’eau',
            'Salle conférence 1 <br> Documents à préparer : bilan 2024, procédures qualité'
        ];

        $done = [true, false];

        for ($i = 0; $i < 30; $i++) {
            $task = new Task();
            $task->setTitle($tasksTitle[array_rand($tasksTitle)]);
            $task->setContent($tasksContent[array_rand($tasksContent)]);
            $task->toggle($done[array_rand($done)]);
            $task->setUser($users[array_rand($users)]);
            $manager->persist($task);
        }

        // Flush pour insérer les tâches
        $manager->flush();
    }
}
