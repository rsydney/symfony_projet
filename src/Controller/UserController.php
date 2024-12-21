<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Form\UserEditType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    // Route pour l'inscription
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        EntityManagerInterface $entityManager,
        #UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        // Traite la soumission du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hachage du mot de passe
           /* $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $user->getPassword()
            );
            $user->setPassword($hashedPassword);*/

            // Persistance de l'utilisateur en base de données
            $entityManager->persist($user);
            $entityManager->flush();

            // Redirection vers la page de connexion après l'inscription
            return $this->redirectToRoute('app_login');
        }

        return $this->render('user/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Route pour le tableau de bord
    #[Route('/dashboard', name: 'app_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('user/dashboard.html.twig');
    }

    // Route pour la liste des utilisateurs
    #[Route('/utilisateurs', name: 'utilisateur_index')]
    public function index(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();  // Récupère tous les utilisateurs depuis la base de données

        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }

    // Route pour modifier un utilisateur
    #[Route('/utilisateur/{id}/edit', name: 'utilisateur_edit')]
    public function edit(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager,
        #UserPasswordHasherInterface $passwordHasher
    ): Response {
        // Crée un formulaire pour éditer les données de l'utilisateur
        $form = $this->createForm(UserEditType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hachage du mot de passe si modifié
            if ($user->getPassword()) {
                /*$hashedPassword = $passwordHasher->hashPassword(
                    $user,
                    $user->getPassword()
                );
                $user->setPassword($hashedPassword);*/
            }

            // Sauvegarde des modifications
            $entityManager->flush();

            // Redirection après modification
            return $this->redirectToRoute('utilisateur_index');
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Route pour supprimer un utilisateur
    #[Route('/utilisateur/{id}/delete', name: 'utilisateur_delete')]
    public function delete(
        User $user,
        EntityManagerInterface $entityManager
    ): Response {
        // Suppression de l'utilisateur
        $entityManager->remove($user);
        $entityManager->flush();

        // Redirection vers la liste des utilisateurs après suppression
        return $this->redirectToRoute('utilisateur_index');
    }
}
