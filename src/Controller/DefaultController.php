<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(): Response
    {
        return $this->render('index.html.twig');
    }

    #[Route('/language/{choice}', name: 'change_language', methods: ['GET'])]
    public function changeLanguage($choice): Response
    {
        return $this->redirectToRoute('app_index', ['_locale' => $choice]);
    }
}