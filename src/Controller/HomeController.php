<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use App\Repository\EvenementRepository;
use App\Repository\ProduitsRepository;
use App\Repository\CategoriesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Publication;
use App\Form\PublicationType;
use App\Repository\PublicationRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
class HomeController extends AbstractController
{

    #[Route('/home',name:'app_home')]
    public function index(ManagerRegistry $doctrine, EvenementRepository $evenementRepository,ProduitsRepository $produitsRepository,CategoriesRepository $categoriesRepository): Response
    {
        $repository= $doctrine->getRepository(Publication::class);
    $publications=$repository->findAll();
        return $this->render('home/index.html.twig', [
            'produits' => $produitsRepository->findAll(),
            'categories' => $categoriesRepository->findAll(),
        'evenements' => $evenementRepository->selectEvents(),
            'publication' => $publications,
        ]);
    }
}