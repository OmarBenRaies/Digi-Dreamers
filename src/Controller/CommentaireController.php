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
    return $this->render('home/index.html.twig', [
        'publication' => $commentaires,
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
        return $this->redirectToRoute('listcom ');
    }

    #[Route('/addcom',name:'addcom')]
    public function add (HttpFoundationRequest $request,ManagerRegistry $doctrine): Response
    {
        $repository= $doctrine->getRepository(Commentaire::class);
        $commentaires=$repository->findAll();
        $commentaire=new Commentaire;
        $form=$this->createForm(CommentaireType::class,$commentaire);
        $form->add('add',SubmitType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted())
        {
            $em=$doctrine->getManager();
            $em->persist($commentaire);
            $em->flush();
            return $this->redirectToRoute('addcom');
        }
        return $this->renderForm('commentaire/addcom.html.twig',['formC'=>$form,'commentaire' => $commentaires]);
    }

    #[Route('/editcom/{id}', name: 'editcom')]
    public function edit(HttpFoundationRequest $request,ManagerRegistry $doctrine,$id ): Response
    {  
        $repository= $doctrine->getRepository(Commentaire::class);
        $commentaires=$repository->find($id);
       $form=$this->createForm(CommentaireType::class,$commentaires);
       $form->add('edit',SubmitType::class);
       $form->handleRequest($request);
       if($form->isSubmitted())
       {
        $em=$doctrine->getManager();
        $em->flush();
        return $this->redirectToRoute('addcom');
       }
       return $this->renderForm('commentaire/editcom.html.twig',['formC'=>$form,'commentaire' => $commentaires]);
    }
}

