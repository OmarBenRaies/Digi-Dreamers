<?php

namespace App\Controller;

use App\Entity\Association;
use App\Repository\AssociationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/associationAPI')]
class AssociationControllerAPIController extends AbstractController
{
    #[Route('/', name: 'app_association_indexJSON', methods: ['GET'])]
    public function indexJSON(AssociationRepository $associationRepository,SerializerInterface $serializer)
    {   $associations = $associationRepository->findAll();

        $json=$serializer->serialize($associations,'json',['groups'=>"associations"]);
        return new Response($json);
    }

    #[Route('/newJSON', name: 'app_association_newJSON', methods: ['GET', 'POST'])]
    public function newJSON(Request $request, SerializerInterface $serializer): Response
    {   $em=$this->getDoctrine()->getManager();

        $association = new Association();
        $association->setNom($request->get('nom'));
        $association->setDescription($request->get('description'));

        $em->persist($association);
        $em->flush();

        $jsonContent=$serializer->serialize($association,'json',['groups'=>"associations"]);

        return new Response(json_encode($jsonContent));
    }


    #[Route('/showJSON/{id}', name: 'app_association_showJSON', methods: ['GET'])]
    public function showJSON($id,SerializerInterface $serializer,AssociationRepository $repo): Response

    {
        $association=$repo->find($id);
        $json=$serializer->serialize($association,'json',['groups'=>'associations']);
        return new Response($json);
    }

    #[Route('/{id}/editJSON', name: 'app_association_editJSON', methods: ['GET', 'POST'])]
    public function edit(Request $request, $id, NormalizerInterface $Normalizer): Response

    {
        $em=$this->getDoctrine()->getManager();
        $association=$em->getRepository(Association::class)->find($id);
        $association->setNom($request->get('nom'));
        $association->setDescription($request->get('desctription'));
        $em->flush();
        $jsonContent = $Normalizer->normalize($association,'json',['groups'=>'associations']);


        return new Response("Association updated successfully".json_encode($jsonContent));
    }


    #[Route('delete/JSON/{id}', name: 'app_association_deleteJSON', methods: ['GET', 'POST'])]
    public function deleteJSON(Request $request, $id,NormalizerInterface $Normalizer) {


        $em=$this->getDoctrine()->getManager();
        $association= $em->getRepository(Association::class)->find($id);


        $em->remove($association);
        $em->flush();
        $jsonContent = $Normalizer->normalize($association,'json',['groups'=>'associations']);
        return new Response("Association deleted successfully".json_encode($jsonContent));
    }

}
