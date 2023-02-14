<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PublicationRepository;
use App\Entity\Publication;
use App\Form\PublicationType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class PublicationController extends AbstractController

{
    
    #[Route('/publication', name: 'app_publication')]
     
    public function index(): Response
    {
        return $this->render('publication/index.html.twig', [
            'controller_name' => 'PublicationController',
        ]);
    }
#[Route('/listpub', name: 'listpub')]
public function list(ManagerRegistry $doctrine): Response
{
    $repository= $doctrine->getRepository(Publication::class);
    $publications=$repository->findAll();
    return $this->render('home/index.html.twig', [
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
        return $this->redirectToRoute('listpub');
    }

    #[Route('/addpub',name:'addpub')]
    public function add (HttpFoundationRequest $request,ManagerRegistry $doctrine): Response
    {
        $repository= $doctrine->getRepository(Publication::class);
        $publications=$repository->findAll();
        $publication=new Publication;
        $form=$this->createForm(PublicationType::class,$publication);
        $form->add('add',SubmitType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted())
        {
            $em=$doctrine->getManager();
            $em->persist($publication);
            $em->flush();
            return $this->redirectToRoute('addpub');
        }
        return $this->renderForm('publication/addpub.html.twig',['formP'=>$form,'publication' => $publications]);
    }

    #[Route('/editpub/{id}', name: 'editpub')]
    public function edit(HttpFoundationRequest $request,ManagerRegistry $doctrine,$id ): Response
    {  
        $repository= $doctrine->getRepository(Publication::class);
        $publications=$repository->find($id);
       $form=$this->createForm(Publicationtype::class,$publications);
       $form->add('edit',SubmitType::class);
       $form->handleRequest($request);
       if($form->isSubmitted())
       {
        $em=$doctrine->getManager();
       // $publications->setCodepub($form->get('CodePub')->getData());
        $em->flush();
        return $this->redirectToRoute('addpub');
       }
       return $this->renderForm('publication/editpub.html.twig',['formP'=>$form,'publication' => $publications]);
    }
}
