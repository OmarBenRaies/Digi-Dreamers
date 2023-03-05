<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Form\CommentaireType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use App\Repository\PublicationRepository;
use App\Form\PublicationType;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Entity\Publication;
use App\Controller\EntityType;


class CommentaireController extends AbstractController
{
    /**
     * @Route("/commentaire", name="app_commentaire")
     */
    public function index(): Response
    {
        return $this->render('commentaire/index.html.twig', [
            'controller_name' => 'CommentaireController',
        ]);
    }

    #[Route('/listcom', name: 'listcom')]
public function list(ManagerRegistry $doctrine): Response
{
    $repository= $doctrine->getRepository(Commentaire::class);
    $commentaires=$repository->findAll();
    return $this->render('commentaire/listcom.html.twig', [
        'commentaire' => $commentaires,
    ]);
} 

#[Route('/deletecom/{id}',name: 'deletecom')]
    public function delete (ManagerRegistry $doctrine,$id):Response
    {  
        $repository=$doctrine->getRepository(Commentaire::class);
        $commentaire=$repository->find($id);
        $em=$doctrine->getManager();
        $em->remove($commentaire);
        $em->flush();
        return $this->redirectToRoute('listcom');
    }

    #[Route('/deletecom2/{id}',name: 'deletecom2')]
    public function delete2 (ManagerRegistry $doctrine,$id):Response
    {  
        $repository=$doctrine->getRepository(Commentaire::class);
        $commentaire=$repository->find($id);
        $em=$doctrine->getManager();
        $em->remove($commentaire);
        $em->flush();
        return $this->redirectToRoute('listpub');
    }

    #[Route('/addcom',name:'addcom')]
    public function add (HttpFoundationRequest $request,ManagerRegistry $doctrine): Response
    {
        $repository= $doctrine->getRepository(Commentaire::class);
        $commentaires=$repository->findAll();
        $commentaire=new Commentaire;
        $form=$this->createForm(CommentaireType::class,$commentaire);
        $form->handleRequest($request);
            if ($form->isSubmitted()&&$form->isValid())
        {
            $em=$doctrine->getManager();
            $em->persist($commentaire);
            $em->flush();
            return $this->redirectToRoute('addcom');
        }
        return $this->renderForm('commentaire/addcom.html.twig',['formC'=>$form,'commentaire' => $commentaires]);
    }

    #[Route('/addcomFront/{id}',name:'addcom2')]
    public function add2 (HttpFoundationRequest $request,ManagerRegistry $doctrine,$id,PublicationRepository $publicationRepository): Response
    {
        $repository= $doctrine->getRepository(Commentaire::class);
        $commentaires=$repository->findAll();
        $commentaire=new Commentaire;
        $commentaire->setPublication($publicationRepository->find($id));
        $form=$this->createForm(CommentaireType::class,$commentaire);
    
        $form->handleRequest($request);
        if ($form->isSubmitted()&&$form->isValid())
        {
            $em=$doctrine->getManager();
            $em->persist($commentaire);
            $em->flush();
            return $this->redirectToRoute('getpubid',['id'=>$id]);
        }
        return $this->renderForm('commentaire/addcomFront.html.twig',['formC'=>$form,'commentaire' => $commentaires]);
    }

    


    #[Route('/editcom/{id}', name: 'editcom')]
    public function edit(HttpFoundationRequest $request,ManagerRegistry $doctrine,$id ): Response
    {  
        $repository= $doctrine->getRepository(Commentaire::class);
        $commentaires=$repository->find($id);
       $form=$this->createForm(CommentaireType::class,$commentaires);
       $form->handleRequest($request);
       if ($form->isSubmitted()&&$form->isValid())
       {
        $em=$doctrine->getManager();
        $em->flush();
        return $this->redirectToRoute('listcom');
       }
       return $this->renderForm('commentaire/editcom.html.twig',['formC'=>$form,'commentaire' => $commentaires]);
    }

    #[Route('/editcom2/{id}', name: 'editcom2')]
    public function edit2(HttpFoundationRequest $request,ManagerRegistry $doctrine,$id ): Response
    {  
        $repository= $doctrine->getRepository(Commentaire::class);
        $commentaires=$repository->find($id);
       $form=$this->createForm(CommentaireType::class,$commentaires);
       $form->handleRequest($request);
       if ($form->isSubmitted()&&$form->isValid())
       {
        $em=$doctrine->getManager();
        $em->flush();
        return $this->redirectToRoute('listpub');
       }
       return $this->renderForm('commentaire/editcomfront.html.twig',['formC'=>$form,'commentaire' => $commentaires]);
    }
}

