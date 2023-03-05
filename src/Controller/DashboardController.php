<?php

namespace App\Controller;

use App\dto\Pie;
use App\Repository\EvenementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/admin/dashboard',name:'app_dashboard')]
    public function index(EvenementRepository $evenementRepository): Response
    {
        $results= $evenementRepository->chartRepository();
        $totalCount = array_reduce($results, function($carry, $result) {
            return $carry + $result['count'];
        }, 0);
        $resultArray = [];
        foreach ($results as $result) {
            $percentage = round(($result['count'] / $totalCount) * 100);
            $obj = new Pie();
            $obj->value = $result['gouv'];
            $obj->valeur = $percentage ;
            $resultArray[] = $obj;
        }


        return $this->render('dashboard/index.html.twig', [
            'controller_name' => 'DashboardController',
            'results'  =>  $resultArray,
        ]);
    }
}
