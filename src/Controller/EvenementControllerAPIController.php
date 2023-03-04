<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Repository\EvenementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;


#[Route('/evenementAPI')]
class EvenementControllerAPIController extends AbstractController
{
    #[Route('/', name: 'app_don_indexJSON', methods: ['GET'])]
    public function indexJSON(EvenementRepository $evenementRepository,SerializerInterface $serializer)
    {   $events = $evenementRepository->findAll();

        $json=$serializer->serialize($events,'json',['groups'=>"events"]);
        return new Response($json);
    }

    #[Route('/newJSON', name: 'app_don_newJSON', methods: ['GET', 'POST'])]
    public function newJSON(Request $request, NormalizerInterface $Normalizer): Response
    {   $em=$this->getDoctrine()->getManager();

        $event = new Evenement();
        $event->setDate($request->get('date'));
        $event->setLieu($request->get('lieu'));
        $event->setNbrParticipant($request->get('nbr_participant'));
        $event->setTitre($request->get('titre'));
        $event->setDescription($request->get('description'));
        $event->setTotal($request->get('total'));
        $event->setPrix($request->get('prix'));
        $event->setUrlImage($request->get('url_image'));





        $em->persist($event);
        $em->flush();

        $jsonContent=$Normalizer->normalize($event,'json',['groups'=>"events"]);

        return new Response(json_encode($jsonContent));
    }


    #[Route('/showJSON/{id}', name: 'app_don_showJSON', methods: ['GET'])]
    public function showJSON($id,SerializerInterface $serializer,EvenementRepository $repo): Response

    {
        $event=$repo->find($id);
        $json=$serializer->serialize($event,'json',['groups'=>'dons']);
        return new Response($json);
    }

    #[Route('/{id}/editJSON', name: 'app_don_editJSON', methods: ['GET', 'POST'])]
    public function edit(Request $request, $id, NormalizerInterface $Normalizer): Response

    {
        $em=$this->getDoctrine()->getManager();
        $event=$em->getRepository(Evenement::class)->find($id);

        $event->setDate($request->get('date'));
        $event->setLieu($request->get('lieu'));
        $event->setNbrParticipant($request->get('nbr_participant'));
        $event->setTitre($request->get('titre'));
        $event->setDescription($request->get('description'));
        $event->setTotal($request->get('total'));
        $event->setPrix($request->get('prix'));
        $event->setUrlImage($request->get('url_image'));
        $em->flush();
        $jsonContent = $Normalizer->normalize($event,'json',['groups'=>'associations']);
        return new Response("Don updated successfully".json_encode($jsonContent));
    }


    #[Route('delete/JSON/{id}', name: 'app_association_deleteJSON', methods: ['GET', 'POST'])]
    public function deleteJSON(Request $request, $id,NormalizerInterface $Normalizer) {


        $em=$this->getDoctrine()->getManager();
        $event= $em->getRepository(Evenement::class)->find($id);
        $em->remove($event);
        $em->flush();
        $jsonContent = $Normalizer->normalize($event,'json',['groups'=>'associations']);
        return new Response("Association deleted successfully".json_encode($jsonContent));
    }



}
