<?php

namespace App\Controller;

use App\Entity\Don;
use App\Repository\DonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/donAPI')]

class DonControllerAPIController extends AbstractController
{
    #[Route('/', name: 'app_don_indexJSON', methods: ['GET'])]
    public function indexJSON(DonRepository $donRepository,SerializerInterface $serializer)
    {   $dons = $donRepository->findAll();

        $json=$serializer->serialize($dons,'json',['groups'=>"dons"]);
        return new Response($json);

    }

    #[Route('/newJSON', name: 'app_don_newJSON', methods: ['GET', 'POST'])]
    public function newJSON(Request $request, NormalizerInterface $Normalizer): Response
    {   $em=$this->getDoctrine()->getManager();

        $don = new Don();
        $don->setSomme($request->get('somme'));
        $don->setEvenement($request->get('evenement'));
        $don->setAssociation($request->get('association'));



        $em->persist($don);
        $em->flush();

        $jsonContent=$Normalizer->normalize($don,'json',['groups'=>"dons"]);

        return new Response(json_encode($jsonContent));
    }


    #[Route('/showJSON/{id}', name: 'app_don_showJSON', methods: ['GET'])]
    public function showJSON($id,SerializerInterface $serializer,DonRepository $repo): Response

    {
        $don=$repo->find($id);
        $json=$serializer->serialize($don,'json',['groups'=>'dons']);
        return new Response($json);
    }

    #[Route('/{id}/editJSON', name: 'app_don_editJSON', methods: ['GET', 'POST'])]
    public function edit(Request $request, $id, NormalizerInterface $Normalizer): Response

    {
        $em=$this->getDoctrine()->getManager();
        $don=$em->getRepository(Don::class)->find($id);
        $don->setSomme($request->get('somme'));
        $don->setEvenement($request->get('evenement'));
        $don->setAssociation($request->get('association'));
        $em->flush();
        $jsonContent = $Normalizer->normalize($don,'json',['groups'=>'associations']);
        return new Response("Don updated successfully".json_encode($jsonContent));
    }


    #[Route('delete/JSON/{id}', name: 'app_association_deleteJSON', methods: ['GET', 'POST'])]
    public function deleteJSON(Request $request, $id,NormalizerInterface $Normalizer) {


        $em=$this->getDoctrine()->getManager();
        $don= $em->getRepository(Don::class)->find($id);
        $em->remove($don);
        $em->flush();
        $jsonContent = $Normalizer->normalize($don,'json',['groups'=>'associations']);
        return new Response("Association deleted successfully".json_encode($jsonContent));
    }
}
