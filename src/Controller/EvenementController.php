<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Entity\User;
use App\Form\EvenementType;
use App\Repository\EvenementRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\dto\Pie;
use Knp\Component\Pager\PaginatorInterface;



class EvenementController extends AbstractController
{

    #[Route('/admin/evenement', name: 'app_evenement_index', methods: ['GET'])]
    public function index(Request $request,EvenementRepository $evenementRepository,PaginatorInterface $paginator): Response
    {       $evenements=$evenementRepository->findAll();
            $evenements = $paginator->paginate(
            $evenements,
            $request->query->getInt('page', 1),
            2
        );
        return $this->render('evenement/index.html.twig', [
            'evenements' => $evenements,
        ]);
    }

    #[Route('/admin/evenement/new', name: 'app_evenement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EvenementRepository $evenementRepository): Response
    {
        $evenement = new Evenement();
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

       // dd($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $latitude = $request->request->get('latitude');
            $longitude = $request->request->get('longitude');
            $lieu=$evenement->getLieu();

            $parts = explode(",", $lieu);
            $governorate = trim($parts[1]);
            $evenement->setGouv($governorate);
            $evenement->setLat($latitude);
            $evenement->setLon($longitude);

            // uploads image
      //
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

    #[Route('/admin/evenement/show/{id}', name: 'app_evenement_show', methods: ['GET'])]
    public function show(Evenement $evenement): Response
    {
        return $this->render('evenement/show.html.twig', [
            'p' => $evenement,
        ]);
    }

    #[Route('/admin/evenement/{id}/edit', name: 'app_evenement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request,$id): Response
    {   $em=$this->getDoctrine()->getManager();
        $evenement=$em->getRepository(Evenement::class)->find($id);
        $form = $this->createForm(EvenementType::class, $evenement);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $latitude = $request->request->get('latitude');
            $longitude = $request->request->get('longitude');
            $lieu=$evenement->getLieu();
            $parts = explode(",", $lieu);
            $governorate = trim($parts[1]);
            $evenement->setGouv($governorate);
            $evenement->setLat($latitude);
            $evenement->setLon($longitude);

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

    #[Route('/admin/evenement/{id}', name: 'app_evenement_delete')]
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
            'evenements' => $evenementRepository->selectEvents1(),
        ]);
    }

    #[Route('/front/show/{id}', name: 'app_evenement_frontShow', methods: ['GET'])]
    public function show2(Evenement $evenement): Response
    {
        return $this->render('evenement/frontShow.html.twig', [
            'p' => $evenement,
        ]);
    }

    #[Route('/front/participer/{id}', name: 'app_evenement_participer', methods: ['GET'])]
    public function participer($id): Response
    {
        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository(Evenement::class)->find($id);

        
        
        $usr = $em->getRepository(User::class)->find(1);
        if ($event->getNbrParticipant() > 0) {
            $event->setNbrParticipant($event->getNbrParticipant() - 1) ;
                $event->addUser($usr);
            $event->setTotal($event->getTotal() + $event->getPrix());
            $em->flush();

        }

        return $this->redirectToRoute('app_evenement_frontShow',['id'=>$id], Response::HTTP_SEE_OTHER);
    }


    #[Route('/event/search', name: 'Evenement_search' )]
    public function searchAction(Request $request,EntityManagerInterface $em)
    {

        $requestString = $request->get('q');
        $evenements =  $em->getRepository(Evenement::class)->findEntitiesByString($requestString);

        if(!count($evenements)) {
            $result['evenements']['error'] = "Aucun événement trouvé  ";
        } else {
            $result['evenements'] = $this->getRealEntities($evenements);

        }

        return new Response(json_encode($result));
    }
    public function getRealEntities($evenements){
        foreach ($evenements as $evenement){
            $realEntities[$evenement->getId()] = [$evenement->getTitre(),$evenement->getDate()];

        }
        return $realEntities;
    }




   /* #[Route('/event/facture/{id}', name: 'show_pdf')]
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
    }*/
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
