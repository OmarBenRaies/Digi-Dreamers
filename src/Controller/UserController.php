<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\User\ChangePasswordType;
use App\Form\User\LoginFormType;
use App\Form\User\ResetPasswordType;
use App\Form\User\UpdateImageType;
use App\Form\User\UpdateType;
use App\Form\User\UserType;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class UserController extends AbstractController
{


    #[Route('/signup', name: 'app_add_user')]
    public function Add(MailerInterface $mailer,Request $request,ManagerRegistry $doctrine,ValidatorInterface $validator,UserRepository $userRepository,UserPasswordHasherInterface $passwordHasher): Response
    {
        $user=new User();
        $user->setVerified(0);
        $user->setVerificationCode("");
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

            $verificationLink = "http://localhost:8000/verifyEmail/".$user->getEmail();


            $transport = Transport::fromDsn('smtp://nour.benabderrahmen@esprit-tn.com:Nba26042001@smtp.office365.com:587');
            $mailer = new Mailer($transport);
            $email = (new Email());
            $email->from('nour.benabderrahmen@esprit-tn.com');
            $email->to($user->getEmail());
            $email->subject('Code de vérification de votre compte');

            $email->html("<div>Presque terminé! Pour finaliser votre inscription à LotusCare, il nous suffit de vérifier votre adresse e-mail.Cliquez sur le lien <a target='_blank' href=".$verificationLink.">Verifier</a></div>");
            $mailer->send($email);



            return $this->render('user/create.html.twig', array(
                'userform'=>$Form->createView(),
                'errors'=>array(),
                'message'=>"Presque terminé, Pour finaliser votre inscription à LotusCare, il nous suffit de vérifier votre adresse e-mail. Nous venons d'envoyer un lien de confirmation à votre email"
            ));
        }

        return $this->render('user/create.html.twig', array(
            'userform'=>$Form->createView(),
            'errors'=>array()
        ));
    }

    #[Route('/verifyEmail/{email}', name: 'app_verifyEmail')]
    public function verifyEmail(string $email,UserRepository $userRepository,ManagerRegistry $doctrine): Response
    {
        $user = $userRepository->findOneBy(['email' => $email]);
        if($user){
            $user->setVerified(1);
            $ImagePath = $this->getParameter('kernel.project_dir') . '/public/uploads/users/'.$user->getImage();
            $imageFile = new File($ImagePath);
            $user->setImageFile($imageFile);

            $em=$doctrine->getManager();
            $em->flush();

            return $this->render('user/verified.html.twig', array(
                'status'=>'success',
            ));
        }
        return $this->render('user/verified.html.twig', array(
            'status'=>'error',
        ));
    }

    #[Route('/forgetPassword', name: 'app_forgetPassword')]
    public function forgetPassword(Request $request,UserRepository $userRepository,ManagerRegistry $doctrine): Response
    {

        $user = new User();
        $Form=$this->createForm(ResetPasswordType::class,$user);
        $Form->handleRequest($request);



        if ($Form->isSubmitted()){
            $user = $userRepository->findOneBy(['email' => $user->getEmail()]);
            if($user){
                //generating verification code
                $verificationCode =rand(100000,1000000);
                $em = $doctrine->getManager();
                $user->setVerificationCode($verificationCode);

                //fixing imageFile error
                $ImagePath = $this->getParameter('kernel.project_dir') . '/public/uploads/users/'.$user->getImage();
                $imageFile = new File($ImagePath);
                $user->setImageFile($imageFile);
                $em->flush();

                //sending verification code
                $transport = Transport::fromDsn('smtp://nour.benabderrahmen@esprit-tn.com:Nba26042001@smtp.office365.com:587');
                $mailer = new Mailer($transport);
                $email = (new Email());
                $email->from('nour.benabderrahmen@esprit-tn.com');
                $email->to($user->getEmail());
                $email->subject('Reset password code');
                $email->html("<div>Voici le code de verification pour changer votre mot de passe : ".$verificationCode." </div>");
                $mailer->send($email);

                return $this->redirectToRoute('app_verifyCode', array('email' => $user->getEmail()));
            }
            return $this->render('user/forget_password.html.twig',array(
                'form'=>$Form->createView(),
                'UserNotFound'=>true
            ));
        }



        return $this->render('user/forget_password.html.twig',array(
            'form'=>$Form->createView()
        ));
    }

    #[Route('/verifyCode/{email}', name: 'app_verifyCode')]
    public function verifyCode(string $email,Request $request,UserRepository $userRepository,ManagerRegistry $doctrine): Response
    {

        return $this->render('user/verify_code.html.twig',array(
            'email'=>$email
        ));
    }

    #[Route('/validateCode', name: 'app_validate_code')]
    public function validateCode(Request $request,UserRepository $userRepository,ManagerRegistry $doctrine): Response
    {

        $code = $request->query->get('code', 'null');
        $email =  $request->query->get('email', 'null');
        if($code!='null' && $email!='null'){
            $user = $userRepository->findOneBy(['email' => $email]);
            if($user->getVerificationCode()==$code){
                return $this->redirectToRoute('app_resetPassword',array('email'=>$email));
            }
        }
        return $this->redirectToRoute('app_verifyCode',array('email'=>$email));

    }

    #[Route('/resetPassword/{email}', name: 'app_resetPassword')]
    public function resetPassword(string $email,Request $request,UserRepository $userRepository,ManagerRegistry $doctrine,UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $Form=$this->createForm(ChangePasswordType::class,$user);
        $Form->handleRequest($request);

        if ($Form->isSubmitted()){
            if($user->getPassword() === $user->getConfirmPassword()){
                $em = $doctrine->getManager();

                $userWithEmail = $userRepository->findOneBy(['email' => $email]);
                $hashedPassword = $passwordHasher->hashPassword($user,$user->getPassword());

                $userWithEmail->setPassword($hashedPassword);

                //fixing imageFile problem
                $ImagePath = $this->getParameter('kernel.project_dir') . '/public/uploads/users/'.$userWithEmail->getImage();
                $imageFile = new File($ImagePath);
                $userWithEmail->setImageFile($imageFile);

                $em->flush();
                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('user/resetPassword.html.twig',
            array(
                'email'=>$email,
                'form'=>$Form->createView()
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
            $initialImage = $user->getImage();
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
                $ImagePath = $this->getParameter('kernel.project_dir') . '/public/uploads/users/'.$initialImage;
                $imageFile = new File($ImagePath);
                $user->setImageFile($imageFile);

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


    #[Route('/profile', name: 'app_profile')]
    public function Profile(Request $request,ManagerRegistry $doctrine,UserRepository $userRepository): Response
    {

        $imageForm = $this->createForm(UpdateImageType::class, $this->getUser());
        $imageForm->handleRequest($request);

        $updateForm = $this->createForm(UpdateType::class, $this->getUser());
        $updateForm->handleRequest($request);

        if ($imageForm->isSubmitted() && $imageForm->isValid()) {
            $em = $doctrine->getManager();
            $em->flush();
        }

        if ($updateForm->isSubmitted() && $updateForm->isValid()) {
            $em = $doctrine->getManager();
            $ImagePath = $this->getParameter('kernel.project_dir') . '/public/uploads/users/'.$this->getUser()->getImage();
            $imageFile = new File($ImagePath);
            $this->getUser()->setImageFile($imageFile);




            $userWithSameEmail = $userRepository->findOneBy(['email' => $this->getUser()->getEmail()]);
            $userWithSameCin = $userRepository->findOneBy(['cin' => $this->getUser()->getCin()]);


            if($userWithSameCin && $userWithSameCin->getId()!=$this->getUser()->getId()){
                return $this->render('user/profile.html.twig', array(
                    'imageForm'=>$imageForm->createView(),
                    'updateForm'=>$updateForm->createView(),
                    'sameCin'=>true
                ));
            }

            $em->flush();
        }


        return $this->render('user/profile.html.twig', array(
            'imageForm'=>$imageForm->createView(),
            'updateForm'=>$updateForm->createView(),
        ));
    }




}
