<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\User\LoginFormType;
use App\Form\User\LoginType;
use App\Form\User\UpdateType;
use App\Form\User\UserType;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends AbstractController
{

    
#[Route('/signup', name: 'app_add_user')]
public function Add(Request $request,ManagerRegistry $doctrine,ValidatorInterface $validator,UserRepository $userRepository,UserPasswordHasherInterface $passwordHasher): Response
{
    $user=new User();
    $user->setVerified(0);
    $user->setRoles(['ROLE_USER']);
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

        $plaintextPassword = $user->getPassword();

        // hash the password (based on the security.yaml config for the $user class)
        $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $plaintextPassword
        );
        $user->setPassword($hashedPassword);

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


    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // This method is empty, since Symfony's logout functionality is handled automatically by the security system.
    }

    #[Route('/login', name: 'app_login')]
    public function login(Request $request, UserRepository $userRepository,AuthenticationUtils $authenticationUtils, UserPasswordHasherInterface $passwordHasher)
    {
        $user = new User();
        $form = $this->createForm(LoginFormType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $userRepository->findOneBy(['email' => $user->getEmail()]);

            if (!$user) {
                $this->addFlash('danger', 'Email address not found');
                return $this->redirectToRoute('app_login');
            }

            if (!$passwordHasher->isPasswordValid($user, $user->getPassword())) {
                $this->addFlash('danger', 'Invalid password');
                return $this->redirectToRoute('app_login');
            }

            // Authentication successful
            $this->addFlash('success', 'Welcome '.$user->getEmail());
            return $this->redirectToRoute('app_home');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('user/login.html.twig', [
            'lastUsername'=> $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/access-denied', name: 'access_denied')]
    public function accessDenied(){
        return $this->render('user/access_denied.html.twig');
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
