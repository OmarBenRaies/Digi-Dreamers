<?php

namespace App\Controller;

use App\Entity\Commentaire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PublicationRepository;
use App\Entity\Publication;
use App\Form\PublicationType;
use App\Form\CommentaireType;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;




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
public function list(ManagerRegistry $doctrine): Response
{
    $repository= $doctrine->getRepository(Publication::class);
    $publications=$repository->findAll();
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
    public function show_id(ManagerRegistry $doctrine,ManagerRegistry $doc, $id): Response
    {
        $repository = $doctrine->getRepository(Publication::class);
        $publications = $repository->find($id);

        $commentaire= $publications->getCommentaires();
        return $this->render('publication/detailspub.html.twig', [
            'Publication' => $publications,
            'commentaire'  => $commentaire,
        ]);
    }
}

