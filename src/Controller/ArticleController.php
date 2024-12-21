<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{
    #[Route('/articles', name: 'article_index')]
    public function index(Request $request, EntityManagerInterface $entityManager, PaginatorInterface $paginator): Response
    {
        // Récupérer tous les articles avec une requête
        $queryBuilder = $entityManager->getRepository(Article::class)->createQueryBuilder('a');
        
        // Pagination
        $articles = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1), // Page actuelle
            10 // Articles par page
        );

        // Rendu de la vue avec la liste paginée des articles
        return $this->render('article/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/articles/{id}', name: 'article_show')]
    public function show(EntityManagerInterface $entityManager, int $id): Response
    {
        // Récupérer un article par son ID
        $article = $entityManager->getRepository(Article::class)->find($id);

        if (!$article) {
            throw $this->createNotFoundException('Article introuvable.');
        }

        // Rendu de la vue pour afficher un article spécifique
        return $this->render('article/show.html.twig', [
            'article' => $article,
        ]);
    }

    #[Route('/articles/ajouter', name: 'article_create')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Créer une instance de l'entité Article
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);

        // Gérer la requête et le formulaire
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Sauvegarder l'article dans la base de données
            $entityManager->persist($article);
            $entityManager->flush();

            // Message flash et redirection vers la liste des articles
            $this->addFlash('success', 'Article créé avec succès.');
            return $this->redirectToRoute('article_index');
        }

        // Afficher le formulaire
        return $this->render('article/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}
