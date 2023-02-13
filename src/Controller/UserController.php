<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\User\LoginType;
use App\Form\User\UpdateType;
use App\Form\User\UserType;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends AbstractController
{

#[Route('/signup', name: 'app_add_user')]
public function Add(Request $request,ManagerRegistry $doctrine,ValidatorInterface $validator,UserRepository $userRepository): Response
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

        $userWithSameEmail = $userRepository->findOneBy(['email' => $user->getEmail()]);
        $userWithSameCin = $userRepository->findOneBy(['cin' => $user->getCin()]);

        if($userWithSameEmail){
            return $this->render('user/create.html.twig', array(
                'userform'=>$Form->createView(),
                'errors'=>array(),
                'sameEmail'=>true
            ));
        }

        if($userWithSameCin){
            return $this->render('user/create.html.twig', array(
                'userform'=>$Form->createView(),
                'errors'=>array(),
                'sameCin'=>true
            ));
        }

        $em->persist($user);
        $em->flush();

        return $this->render('user/create.html.twig', array(
            'userform'=>$Form->createView(),
            'errors'=>array(),
            'message'=>"Presque terminé, Pour finaliser votre inscription à LotusCare, il nous suffit de vérifier votre adresse e-mail. Nous venons d'envoyer un code de confirmation à votre email"
        ));
    }

    return $this->render('user/create.html.twig', array(
        'userform'=>$Form->createView(),
        'errors'=>array()
    ));
}

#[Route('/login', name: 'app_login')]
public function Login(Request $request,UserRepository $userRepository,ValidatorInterface $validator): Response
    {
        $user=new User();
        $loginForm=$this->createForm(LoginType::class,$user);
        $loginForm->handleRequest($request);

        $errors = $validator->validate($user);

        if(count($errors) > 0){
            return $this->render('user/login.html.twig', array(
                'loginform'=>$loginForm->createView(),
                'errors'=>$errors
            ));
        }

        if ($loginForm->isSubmitted()&&$loginForm->isValid())/*verifier */
        {
            $userInDb = $userRepository->findOneBy(['email' => $user->getEmail()]);
            if(!$userInDb){
                return $this->render('user/login.html.twig', array(
                    'loginform'=>$loginForm->createView(),
                    'errors'=>array(),
                    'message'=>"Utilisateur non trouvé dans notre base de données"
                ));
            }

            if($userInDb->getPassword() != $user->getPassword()){
                return $this->render('user/login.html.twig', array(
                    'loginform'=>$loginForm->createView(),
                    'errors'=>array(),
                    'message'=>"Mot de passe incorrect"
                ));
            }
            if($userInDb->getRole() == "admin"){
                return $this->redirectToRoute('app_dashboard');
            }
            return $this->redirectToRoute('app_home');
        }

        return $this->render('user/login.html.twig', array(
            'loginform'=>$loginForm->createView(),
            'errors'=>array()
        ));
    }

#[Route('/dashboard/users', name: 'app_users_admin')]
public function DisplayUsersAdmin(UserRepository $userRepository): Response
{
    $users = $userRepository->findAll();
    return $this->render('user/display_admin.html.twig', array(
        'users'=>$users,
    ));
}

#[Route('/users/update/{id}', name: 'app_users_update')]
public function Update(int $id,Request $request,UserRepository $userRepository,ManagerRegistry $doctrine,ValidatorInterface $validator): Response
{

        $user=$userRepository->find($id);
        $Form=$this->createForm(UpdateType::class,$user);
        $Form->handleRequest($request);

        $errors = $validator->validate($user);

        if(count($errors) > 0){
            return $this->render('user/update.html.twig', array(
                'userform'=>$Form->createView(),
                'errors'=>$errors
            ));
        }

        if ($Form->isSubmitted()&&$Form->isValid())/*verifier */
        {
            $em=$doctrine->getManager();

            $userWithSameEmail = $userRepository->findOneBy(['email' => $user->getEmail()]);
            $userWithSameCin = $userRepository->findOneBy(['cin' => $user->getCin()]);

            if($userWithSameEmail && $userWithSameEmail->getId()!=$user->getId()){
                return $this->render('user/update.html.twig', array(
                    'userform'=>$Form->createView(),
                    'errors'=>array(),
                    'sameEmail'=>true
                ));
            }

            if($userWithSameCin && $userWithSameCin->getId()!=$user->getId()){
                return $this->render('user/update.html.twig', array(
                    'userform'=>$Form->createView(),
                    'errors'=>array(),
                    'sameCin'=>true
                ));
            }

            $em->flush();

            return $this->redirectToRoute('app_users_admin');
        }

        return $this->render('user/update.html.twig', array(
            'userform'=>$Form->createView(),
            'errors'=>array()
        ));
}

#[Route('/users/delete', name: 'app_users_delete')]
public function delete(Request $request,UserRepository $userRepository,ManagerRegistry $doctrine): Response
{
    $id = $request->query->get('id', 'null');
    if($id!='null'){
        $em = $doctrine->getManager();
        $user = $userRepository->find($id);
        $em->remove($user);
        $em->flush();
        return $this->redirectToRoute('app_users_admin');
    }
    return $this->redirectToRoute('app_users_admin');
}


}
