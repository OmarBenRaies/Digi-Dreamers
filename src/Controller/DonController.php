<?php

namespace App\Controller;

use App\Entity\Don;
use App\Entity\Evenement;
use App\Form\DonType;
use App\Repository\DonRepository;
use App\Repository\EvenementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/don')]
class DonController extends AbstractController
{
    #[Route('/', name: 'app_don_index', methods: ['GET'])]
    public function index(DonRepository $donRepository): Response
    {
        return $this->render('don/index.html.twig', [
            'dons' => $donRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_don_new', methods: ['GET', 'POST'])]
    public function new(Request $request, DonRepository $donRepository,EvenementRepository $evenementRepository): Response
    {
        $don = new Don();
        $form = $this->createForm(DonType::class, $don);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $id=$don->getEvenement()->getId();
            $event=$evenementRepository->find($id);
            $event->setTotal(0);
            $donRepository->save($don, true);

            return $this->redirectToRoute('app_don_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('don/new.html.twig', [
            'don' => $don,
            'f' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_don_show', methods: ['GET'])]
    public function show(Don $don): Response
    {
        return $this->render('don/show.html.twig', [
            'p' => $don,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_don_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Don $don, DonRepository $donRepository): Response
    {
        $form = $this->createForm(DonType::class, $don);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $donRepository->save($don, true);

            return $this->redirectToRoute('app_don_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('don/edit.html.twig', [
            'don' => $don,
            'f' => $form,
        ]);
    }

    #[Route('/delete/{id}', name: 'app_don_delete')]
    public function delete(Request $request, $id): Response
    {
        $don= $this->getDoctrine()->getRepository(Don::class)->find($id);


        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($don);
        $entityManager->flush();
        $response = new Response();
        $response->send();
        return $this->redirectToRoute('app_don_index');

    }




    #[Route('/Total/all', name: 'app_don_total')]
    public function getEventTotal(Request $request, EvenementRepository $eventRepository)
    {
        $eventId = $request->query->get('id');

        $event = $eventRepository->find($eventId);

        if (!$event) {
            return new JsonResponse(['error' => 'Event not found'], 404);
        }

        $total = $event->getTotal();

        return new JsonResponse(['total' => $total]);
    }

}

