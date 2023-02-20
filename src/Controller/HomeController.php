<?php

namespace App\Controller;
use App\Repository\ProduitsRepository;
use App\Repository\CategoriesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{

    /**
     * @Route("/home", name="app_home")
     */
    public function index( ProduitsRepository $produitsRepository,CategoriesRepository $categoriesRepository): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'produits' => $produitsRepository->findAll(),
            'categories' => $categoriesRepository->findAll(),
        ]);
    }
}
