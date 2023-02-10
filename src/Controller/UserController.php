<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends AbstractController
{
    #[Route('/users/', name: 'app_users')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }



#[Route('/users/create', name: 'app_add_user')]
public function Add(Request $request,ManagerRegistry $doctrine,ValidatorInterface $validator): Response
{
    $user=new User();
    $user->setVerified(0);
    $Form=$this->createForm(UserType::class,$user);
    $Form->handleRequest($request);

    $errors = $validator->validate($user);

    if(count($errors) > 0){
        return $this->render('user/create.html.twig', array(
            'userform'=>$Form->createView(),
            'errors'=>$errors
        ));
    }

    if ($Form->isSubmitted()&&$Form->isValid())/*verifier */
    {
        $em=$doctrine->getManager();
        $em->persist($user);
        $em->flush();
        return $this->redirectToRoute('app_users');
    }

    return $this->render('user/create.html.twig', array(
        'userform'=>$Form->createView(),
        'errors'=>array()
    ));
}


}
