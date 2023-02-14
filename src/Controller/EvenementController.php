<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Form\EvenementType;
use App\Repository\EvenementRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/evenement')]
class EvenementController extends AbstractController
{

    #[Route('/', name: 'app_evenement_index', methods: ['GET'])]
    public function index(EvenementRepository $evenementRepository): Response
    {
        return $this->render('evenement/index.html.twig', [
            'evenements' => $evenementRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_evenement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EvenementRepository $evenementRepository): Response
    {
        $evenement = new Evenement();
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // uploads image
            $uploadFile=$form['url_image']->getData();
            $filename=md5(uniqid()).'.'.$uploadFile->guessExtension();//cryptage d image


            $uploadFile->move($this->getParameter('kernel.project_dir').'/public/uploads/event_image',$filename);
            $evenement->setUrlImage($filename);

            $evenementRepository->save($evenement, true);

            return $this->redirectToRoute('app_evenement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('evenement/new.html.twig', [
            'evenement' => $evenement,
            'f' => $form,
        ]);
    }

    #[Route('show/{id}', name: 'app_evenement_show', methods: ['GET'])]
    public function show(Evenement $evenement): Response
    {
        return $this->render('evenement/show.html.twig', [
            'evenement' => $evenement,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_evenement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request,$id): Response
    {   $em=$this->getDoctrine()->getManager();
        $evenement=$em->getRepository(Evenement::class)->find($id);
        $form = $this->createForm(EvenementType::class, $evenement);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // uploads image
            $uploadFile=$form['url_image']->getData();
            $filename=md5(uniqid()).'.'.$uploadFile->guessExtension();//cryptage d image


            $uploadFile->move($this->getParameter('kernel.project_dir').'/public/uploads/event_image',$filename);
            $evenement->setUrlImage($filename);

            $em->flush();

            return $this->redirectToRoute('app_evenement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('evenement/edit.html.twig', [
            'evenement' => $evenement,
            'f' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_evenement_delete')]
    public function delete(Request $request, $id) {
        $evenement= $this->getDoctrine()->getRepository(Evenement::class)->find($id);


        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($evenement);
        $entityManager->flush();
        $response = new Response();
        $response->send();
        return $this->redirectToRoute('app_evenement_index');
    }

    #[Route('/front/events', name: 'app_evenement_index2', methods: ['GET'])]
    public function index2(EvenementRepository $evenementRepository): Response
    {
        return $this->render('evenement/index2.html.twig', [
            'evenements' => $evenementRepository->findAll(),
        ]);
    }

    #[Route('/front/show/{id}', name: 'app_evenement_frontShow', methods: ['GET'])]
    public function show2(Evenement $evenement): Response
    {
        return $this->render('evenement/frontShow.html.twig', [
            'p' => $evenement,
        ]);
    }




    #[Route('/event/facture/{id}', name: 'show_pdf')]
    public function generatePdfAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository(Evenement::class)->find($id);


        $html = $this->renderView('evenement/mypdf.html.twig', [

                'titre'=>$event->getTitre(),
                'date' => $event->getDate(),
                'lieu' => $event->getLieu(),
                'image_url' => $event->getUrlImage(),
                'prix' => $event->getPrix(),


        ]);

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'your_pdf_file.pdf';

        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$filename.'"',
            ]
        );
    }
    /*#[Route('/list/search', name: 'app_search')]

    public function searchAction(Request $request)
    {
        $query = $request->query->get('q');
        $location = $request->query->get('lieu');

        $results = $this->entityManager->getRepository(Evenement::class)->search($query, $location);

        return new JsonResponse([
            'query' => $query,
            'location' => $location,
            'results' => $results,
        ]);
    }*/

    /*
    public function Showabsencepdf(\Symfony\Component\HttpFoundation\Request $req, $id)
    {



        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('evenement/mypdf.html.twig', [
            'p'=>$event,



        ]);


        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (force download)
        $dompdf->stream("mypdf.pdf", [
            "Attachment" => true
        ]);


    }*/

}
