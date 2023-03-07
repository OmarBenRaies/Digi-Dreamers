<?php

namespace App\Controller;

use App\Entity\PubLike;
use App\Entity\Commentaire;
use App\Entity\Publication;
use App\Entity\User;
use App\Form\CommentaireType;
use App\Form\PublicationType;
use App\Repository\PubLikeRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use App\Repository\PublicationRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use App\Services\QrcodeService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;



class PublicationController extends AbstractController

{

    #[Route('/publication', name: 'app_publication')]
     
    public function index(): Response
    {
        return $this->render('publication/index.html.twig', [
            'controller_name' => 'PublicationController',
        ]);
    }


    #[Route('/back', name: 'back')]
public function list2(ManagerRegistry $doctrine): Response
{
    $repository= $doctrine->getRepository(Publication::class);
    $publications=$repository->findAll();
    return $this->render('publication/index.html.twig', [
        'publication' => $publications,
    ]);
} 

#[Route('/ajoutpub', name: 'ajoutpub')]
public function list3(ManagerRegistry $doctrine): Response
{
    $repository= $doctrine->getRepository(Publication::class);
    $publications=$repository->findAll();
    return $this->render('publication/addpub.html.twig', [
        'publication' => $publications,
    ]);
} 


#[Route('/ajoutpub',name:'ajoutpub')]
    public function add2 (HttpFoundationRequest $request,ManagerRegistry $doctrine, SluggerInterface $slugger): Response
    {
        $repository= $doctrine->getRepository(Publication::class);
        $publications=$repository->findAll();
        $publication=new Publication;
        $form=$this->createForm(PublicationType::class,$publication);
        $publication->setAllDay(1); 
        $publication->setBackgroundColor("#5c9665");
        $publication->setTextColor("#000000");
        $publication->setBorderColor("#F9ED69");
        $form->handleRequest($request);
       // if ($form->isSubmitted())
        {
            //$date = new \DateTime('@',strtotime('now'));
            $em=$doctrine->getManager();
            $em->persist($publication);
    {
        
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $brochureFile */
            $UrlImagePub = $form->get('UrlImagePub')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($UrlImagePub) {
                $originalFilename = pathinfo($UrlImagePub->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$UrlImagePub->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $UrlImagePub->move(
                        $this->getParameter('Publication_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $publication->setUrlImagePub($newFilename);
            }

            $em->flush();
            return $this->redirectToRoute('listpub');
        }
        return $this->renderForm('publication/addpub.html.twig',['formP'=>$form,'publication' => $publications]);
    }
 
    }  

    }

#[Route('/listpub', name: 'listpub')]
public function list(ManagerRegistry $doctrine,Request $request,PaginatorInterface $paginator,): Response
{
    $repository= $doctrine->getRepository(Publication::class);
    $publications=$repository->findAll();
    

    $publications = $paginator->paginate(
        $publications, /* query NOT result */
        $request->query->getInt('page', 1), /*page number*/
        3 /*limit per page*/
    );
    return $this->render('publication/listpub.html.twig', [
        'publication' => $publications,
    ]);
}


#[Route('/deletepub/{id}',name: 'deletepub')]
    public function delete (ManagerRegistry $doctrine,$id):Response
    {  
        $repository=$doctrine->getRepository(Publication::class);
        $publication=$repository->find($id);
        $em=$doctrine->getManager();
        $em->remove($publication);
        $em->flush();
        return $this->redirectToRoute('back');
    }

    #[Route('/deletepub2/{id}',name: 'deletepub2')]
    public function delete2 (ManagerRegistry $doctrine,$id):Response
    {  
        $repository=$doctrine->getRepository(Publication::class);
        $publication=$repository->find($id);
        $em=$doctrine->getManager();
        $em->remove($publication);
        $em->flush();
        return $this->redirectToRoute('listpub');
    }


    #[Route('/addpub',name:'addpub')]
    public function add (HttpFoundationRequest $request,ManagerRegistry $doctrine, SluggerInterface $slugger): Response
    {
        $repository= $doctrine->getRepository(Publication::class);
        $publications=$repository->findAll();
        $publication=new Publication;
        $form=$this->createForm(PublicationType::class,$publication);
        $publication->setAllDay(1); 
        $publication->setBackgroundColor("#5c9665");
        $publication->setTextColor("#000000");
        $publication->setBorderColor("#F9ED69");
        
        $form->handleRequest($request);
       // if ($form->isSubmitted())
        {
            //$date = new \DateTime('@',strtotime('now'));
            $em=$doctrine->getManager();
            $em->persist($publication);
    {
        
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $brochureFile */
            $UrlImagePub = $form->get('UrlImagePub')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($UrlImagePub) {
                $originalFilename = pathinfo($UrlImagePub->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$UrlImagePub->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $UrlImagePub->move(
                        $this->getParameter('Publication_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $publication->setUrlImagePub($newFilename);
            }

            $em->flush();
            return $this->redirectToRoute('back');
        }
        return $this->renderForm('publication/ajoutpublication.html.twig',['formP'=>$form,'publication' => $publications]);
    }
 
    }  

    }

    #[Route('/editpub/{id}', name: 'editpub')]
    public function edit(HttpFoundationRequest $request,ManagerRegistry $doctrine,$id ): Response
    {  
       $repository= $doctrine->getRepository(Publication::class);
     $publications=$repository->find($id);
       $form=$this->createForm(Publicationtype::class,$publications);
       $form->handleRequest($request);
       if ($form->isSubmitted()&&$form->isValid())
       {
        $em=$doctrine->getManager();
        $em->flush();
        return $this->redirectToRoute('back');
       }
       return $this->renderForm('publication/editpub.html.twig',['formP'=>$form,'publication' => $publications]);
   }

   #[Route('/editpubfront/{id}', name: 'editpubfront')]
    public function edit2(HttpFoundationRequest $request,ManagerRegistry $doctrine,$id ): Response
    {  
       $repository= $doctrine->getRepository(Publication::class);
     $publications=$repository->find($id);
       $form=$this->createForm(Publicationtype::class,$publications);
       $form->handleRequest($request);
       if ($form->isSubmitted()&&$form->isValid())
       {
        $em=$doctrine->getManager();
        $em->flush();
        return $this->redirectToRoute('listpub');
       }
       return $this->renderForm('publication/editpubfront.html.twig',['formP'=>$form,'publication' => $publications]);
   }

   #[Route('/getpub/{id}', name: 'getpubid')]
    public function show_id(ManagerRegistry $doctrine,ManagerRegistry $doc, $id,QrcodeService $qr): Response
    {

        $qrcode=null;
        $repository = $doctrine->getRepository(Publication::class);
        $publications = $repository->find($id);
        $qrcode=$qr->qrcode($publications->getContenuPub());

        $commentaire= $publications->getCommentaires();
        return $this->render('publication/detailspub.html.twig', [
            'Publication' => $publications,
            'commentaire'  => $commentaire,
            'qrcode'=>$qrcode,
        ]);
    }

    #[Route('/calender', name: 'calenderpub')]

    public function calendar( PublicationRepository $publication)
     {

        $events = $publication->findAll();

        $rdvs = [];

            foreach($events as $event){

                $rdvs[] = [
                    'id' => $event->getId(),
                    'start' => $event->getDatePub()->format('Y-m-d H:i:s'),
                    'title' => $event->getCodePub(),
                    'description' => $event->getContenuPub(),
                   'allDay' => $event->getAllDay(),
                    'backgroundColor' =>$event->getBackgroundColor(),
                'borderColor' => $event->getBorderColor(),
                'textColor' => $event->getTextColor(),

                ];

            }
        
            $data = json_encode($rdvs);

        return $this->render('publication/calenderpub.html.twig',compact('data'));
     }


     #[Route('/likepub/{id}', name: 'likepub')]
     public function isLikedByUser(Publication $publication, ManagerRegistry $doctrine, PubLikeRepository $pubLikeRepository): Response
     {
         $user = $this->getUser();
         if (!$user) {
             return $this->json([
                 'code' => 403,
                 'message' => 'Unauthorized'
             ], 403);
         }
     
         if ($publication->isLikedByUser($user)) {
             $likes = $pubLikeRepository->findOneBy([
                 'publication' => $publication,
                 'user' => $user
             ]);
     
             $em = $doctrine->getManager();
             $em->remove($likes);
             $em->flush();
     
             return $this->json([
                 'code' => 200,
                 'message' => 'Like bien supprimé',
                 'likes' => $pubLikeRepository->count(['publication' => $publication])
             ]);
         }
     
         $likes = new PubLike();
         $likes->setPublication($publication)
             ->setUser($user);
     
         $em = $doctrine->getManager();
         $em->persist($likes);
         $em->flush();
     
         return $this->json([
             'code' => 200,
             'message' => 'Like bien ajouté',
             'likes' => $pubLikeRepository->count(['publication' => $publication])
         ]);
     }
     
     #[Route('/stats', name: 'stats')]
     public function stats(ChartBuilderInterface $chartBuilder,PublicationRepository $publication, PubLikeRepository $pubLike): Response
     {
         $publication = $publication->findAll();
 
        $labels = [];
        $data = [];
 
                 foreach ($publication as $publication) {
 
 
                     $labels[] = $publication->getCodePub();
                     $data[] = [1,2,3,1];
                 
                 }
                 $chart = $chartBuilder->createChart(Chart::TYPE_LINE);           
                 $chart->setData([
                     'labels' => $labels ,
                     'datasets' => [
 
                         [
                             'label' => 'like par pub',
                             'backgroundColor' => 'rgb(255, 99, 132)',
                             'borderColor' => 'rgb(255, 99, 132)',
                             'data' => $data,
                         ],
                     ],
                 ]);
         
                 $chart->setOptions([
                     'scales' => [
                         'y' => [
                             'suggestedMin' => 0,
                             'suggestedMax' => 100,
                         ],
                     ],
                 ]);
                 return $this->render('publication/stats.html.twig', [
                    'chart' => $chart,
         ]);
     }


}