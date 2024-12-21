<?php

namespace App\Controller;

use App\Entity\Client;
use App\Form\ClientType; 
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface; 
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DemandeController extends AbstractController
{
    #[Route('/demandes', name: 'demande_index')]
    public function index(): Response {
        return $this->render('Demande/index.html.twig');
    }
    #[Route('/mes-demandes', name: 'mesdemandes_index')]
    public function mesdemandes(): Response {
        return $this->render('espaceclient/mesdemandes/index.html.twig');
    }
    #[Route('/mes-dettes', name: 'mesdettes_index')]
    public function espaceclientdette(): Response {
        return $this->render('espaceclient/dette/index.html.twig');
    }

} 