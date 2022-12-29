<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
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

    public function navbarTop(CategoryRepository $categoryRepository): Response
    {
        return $this->render('_includes/_navbar.html.twig', [
            'categories' => $categoryRepository->findBy([], ['name' => 'ASC'])
        ]);
    }
}