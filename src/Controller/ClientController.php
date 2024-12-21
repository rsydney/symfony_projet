<?php
// src/Controller/ClientController.php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\User;
use App\Form\ClientType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ClientController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    // Méthode pour afficher la liste des clients
    #[Route('/clients', name: 'client_index')]
    public function index(Request $request, PaginatorInterface $paginator, EntityManagerInterface $entityManager): Response
    {
        // Paramètres de filtrage
        $prenomFilter = $request->query->get('prenom');
        $nomFilter = $request->query->get('nom');
        $telephoneFilter = $request->query->get('telephone');
    
        /* Ajouter les logs pour vérifier les valeurs des filtres
        $this->get('logger')->info('Filtre prénom: ' . $prenomFilter);
        $this->get('logger')->info('Filtre nom: ' . $nomFilter);
        $this->get('logger')->info('Filtre téléphone: ' . $telephoneFilter);*/
    
        // Construction de la requête
        $queryBuilder = $entityManager->getRepository(Client::class)->createQueryBuilder('c')
            ->leftJoin('c.dettes', 'd') // Jointure pour optimiser la récupération des dettes
            ->groupBy('c.id'); // Groupement par client
    
        if ($prenomFilter) {
            $queryBuilder->andWhere('c.prenom LIKE :prenom')
                         ->setParameter('prenom', '%' . $prenomFilter . '%');
        }
    
        if ($nomFilter) {
            $queryBuilder->andWhere('c.nom LIKE :nom')
                         ->setParameter('nom', '%' . $nomFilter . '%');
        }
    
        if ($telephoneFilter) {
            $queryBuilder->andWhere('CAST(c.telephone AS string) LIKE :telephone')
                         ->setParameter('telephone', '%' . $telephoneFilter . '%');
        }
    
        $clientsQuery = $queryBuilder->getQuery();
    
        // Pagination des résultats
        $clients = $paginator->paginate(
            $clientsQuery,
            $request->query->getInt('page', 1), // Page courante
            10 // Limite par page
        );
    
        // Transmettre les résultats à la vue
        return $this->render('client/index.html.twig', [
            'clients' => $clients,
            'prenomFilter' => $prenomFilter,
            'nomFilter' => $nomFilter,
            'telephoneFilter' => $telephoneFilter,
        ]);
    }
    



    // Ajouter un nouveau client
#[Route('/client/add', name: 'client_add')]
public function add(Request $request): Response
{
    $client = new Client();

    // Si le formulaire est soumis
    if ($request->isMethod('POST')) {
        $surname = $request->request->get('surname');
        $nom = $request->request->get('nom');
        $prenom = $request->request->get('prenom');
        $telephone = $request->request->get('telephone');
        $adresse = $request->request->get('adresse');
        
        // Vérification des champs obligatoires
        if (empty($surname)) {
            $this->addFlash('error', "Le champ 'surname' est obligatoire.");
            return $this->redirectToRoute('client_add');
        }
        if (empty($nom) || empty($prenom) || empty($telephone) || empty($adresse)) {
            $this->addFlash('error', "Tous les champs sont obligatoires.");
            return $this->redirectToRoute('client_add');
        }
        
        // Affectation des valeurs à l'entité Client
        $client->setSurname($surname);
        $client->setNom($nom);
        $client->setPrenom($prenom);
        $client->setTelephone($telephone);
        $client->setAdresse($adresse);

        // Gestion du compte utilisateur (optionnel)
        if ($request->request->get('create_user_account')) {
            $login = $request->request->get('login');
            $password = $request->request->get('password');

            // Vérification des champs de compte utilisateur
            if (empty($login) || empty($password)) {
                $this->addFlash('error', "Le login et le mot de passe sont obligatoires pour créer un compte utilisateur.");
                return $this->redirectToRoute('client_add');
            }

            $userAccount = new User();
            $userAccount->setNom($nom);
            $userAccount->setPrenom($prenom);
            $userAccount->setLogin($login);
            $userAccount->setPassword(password_hash($password, PASSWORD_BCRYPT));

            $client->setUserAccount($userAccount);
            $this->entityManager->persist($userAccount);
        }

        // Persist le client dans la base de données
        $this->entityManager->persist($client);
        $this->entityManager->flush();

        // Message de succès
        $this->addFlash('success', 'Le client a été ajouté avec succès.');

        // Redirection vers la liste des clients
        return $this->redirectToRoute('client_index');
    }

    // Si le formulaire n'est pas soumis, afficher la page d'ajout
    return $this->render('client/add.html.twig');
}


    // Afficher les dettes d’un client
    #[Route('/clients/{id}/dettes', name: 'client_dettes_show')]
    public function showDettes(int $id, EntityManagerInterface $entityManager): Response
    {
    $client = $entityManager->getRepository(Client::class)->find($id);

    if (!$client) {
        throw $this->createNotFoundException('Client introuvable.');
    }

    return $this->render('client/dettes.html.twig', [
        'client' => $client, // Ajout de 'client' au contexte
        'dettes' => $client->getDettes(),
    ]);
}


    #[Route('/clients/{id}/details', name: 'client_details')]
    public function details(int $id, EntityManagerInterface $entityManager): Response
    {
        $client = $entityManager->getRepository(Client::class)->find($id);

        if (!$client) {
            throw $this->createNotFoundException('Client non trouvé.');
        }

        $client->updateMontantDette();

        return $this->render('client/details.html.twig', [
            'client' => $client,
            'dettes' => $client->getDettes(),
            'montantTotal' => $client->getTotalDettes(),
            'montantVerser' => $client->getMontantVerser(),
            'montantRestant' => $client->getMontantRestant(),

        ]);
    }

}