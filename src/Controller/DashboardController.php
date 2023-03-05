<?php

namespace App\Controller;

use App\dto\Pie;
use App\Repository\ProduitsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    /**
     * @Route("/dashboard", name="app_dashboard")
     */
    public function index(ProduitsRepository $produitsRepository): Response
    {
        $results1= $produitsRepository->chartRepository();
        $totalCount1 = array_reduce($results1, function($carry, $result) {
            return $carry + $result['count'];
        }, 0);
        $resultArray1 = [];

        foreach ($results1 as $result) {
            $percentage = round(($result['count'] / $totalCount1) * 100);
            $obj = new Pie();

            $obj->value = $result['Nom_cat'];
            $obj->valeur = $percentage ;
            $resultArray1[] = $obj;
        }


        return $this->render('dashboard/index.html.twig', [
            'controller_name' => 'DashboardController',
            'results'  =>  $resultArray1,
        ]);
    }


}