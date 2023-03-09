<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class UserAPIController extends AbstractController
{
    #[Route('/api/users/getUsers',name: 'api_getUsers')]
    public function getUsers(UserRepository $userRepository,NormalizerInterface $normalizer)
    {
        $users = $userRepository->findAll();
        $json = $normalizer->normalize($users, 'json',['groups'=>"users"]);
        return $this->json($json);
    }

    #[Route('/api/login', name: 'api_login',methods: ['POST'])]
    public function login(Request $request,UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher,NormalizerInterface $normalizer): Response
    {
        $user = new User();
        // Get the email and password from the request object
        $email = $request->request->get('email');
        $password = $request->request->get('password');

        //controle de saisie
        if(!$email){return $this->json(['status'=> 'error','message' => 'please provide email']);}
        if(!$password){return $this->json(['status'=> 'error','message' => 'please provide password']);}

        $user = $userRepository->findOneBy(['email' => $email]);

        //user not found
        if (!$user) {
            return $this->json([
                'status'=> 'error',
                'message' => 'User not found',
            ]);
        }

        //incorrect password
        if (!$passwordHasher->isPasswordValid($user, $password)) {
            return $this->json([
                'status'=> 'error',
                'message' => 'Invalid password',
            ]);
        }

        // Authentication successful
        $connectedUser = $normalizer->normalize($user, 'json',['groups'=>"users"]);
        return $this->json([
            'status'=> 'success',
            'message' => 'Logged Successfully',
            'user'=>$connectedUser
        ]);
    }

    #[Route('/api/signup', name: 'api_signup',methods: ["POST"])]
    public function signup(ManagerRegistry $doctrine, Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $em = $doctrine->getManager();


        $email = $request->request->get('email');
        $plaintextPassword = $request->request->get('password');
        $nom = $request->request->get('nom');
        $prenom = $request->request->get('prenom');
        $telephone = $request->request->get('telephone');
        $cin = $request->request->get('cin');

        //controle de saisie
        if(!$email){return $this->json(['status'=> 'error','message' => 'please provide email']);}
        if(!$plaintextPassword){return $this->json(['status'=> 'error','message' => 'please provide password']);}
        if(!$nom){return $this->json(['status'=> 'error','message' => 'please provide nom']);}
        if(!$prenom){return $this->json(['status'=> 'error','message' => 'please provide prenom']);}
        if(!$telephone){return $this->json(['status'=> 'error','message' => 'please provide telephone']);}
        if(!$cin){return $this->json(['status'=> 'error','message' => 'please provide cin']);}

        $user = new User();
        $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $plaintextPassword
        );//hash password

        $user->setNom($nom);
        $user->setPrenom($prenom);
        $user->setTelephone($telephone);
        $user->setPassword($hashedPassword);
        $user->setEmail($email);
        $user->setCin($cin);

        $defaultImagePath = $this->getParameter('kernel.project_dir') . '/public/uploads/users/default-image.jpg';
        $imageFile = new File($defaultImagePath);
        $user->setImageFile($imageFile);

        $user->setImage('default-image.jpg');
        $user->setRoles(['ROLE_USER']);
        $user->setVerified(1);
        $user->setVerificationCode(“”);

        $em->persist($user);
        $em->flush();

        return $this->json([
            'status'=> 'success',
            'message' => 'Registered Successfully'
        ]);
    }

    #[Route('/api/sendVerificationCode/{email}',name: 'api_sendVerificationCode')]
    public function sendVerificationCode(string $email,UserRepository $userRepository,ManagerRegistry $doctrine){

        $verificationCode =rand(100000,1000000); //generer le code de verification composé de 6 chiffres
        $em = $doctrine->getManager();
        $user = $userRepository->findOneBy(['email' => $email]); //recuperer l'utilisateur avec l'email


        $user->setVerificationCode($verificationCode); //modifier le code dans la base

        //fixing imageFile error
        $ImagePath = $this->getParameter('kernel.project_dir') . '/public/uploads/users/'.$user->getImage();
        $imageFile = new File($ImagePath);
        $user->setImageFile($imageFile);
        $em->flush();

        //envoyer le code de verifiaction par mail
        $transport = Transport::fromDsn('smtp://nour.benabderrahmen@esprit-tn.com:Nba26042001@smtp.office365.com:587');
        $mailer = new Mailer($transport);
        $mail = (new Email());
        $mail->from('nour.benabderrahmen@esprit-tn.com');
        $mail->to($email);
        $mail->subject('Activate account code');
        $mail->html("<div>Voici le code d'activation de votre compte : ".$verificationCode." </div>");
        $mailer->send($mail);

        return $this->json(['status'=> 'success','message' => 'Email sent Successfully']);



    }

    #[Route('/api/verifyCode/{email}/{code}',name: 'api_verifyCode')]
    public function verifyCode(string $email,string $code,UserRepository $userRepository,ManagerRegistry $doctrine){

        $em = $doctrine->getManager();
        $user = $userRepository->findOneBy(['email' => $email]);

        //verifier le code envoyé est correct ou pas
        if($user->getVerificationCode()==$code){
            //fixing imageFile error
            $ImagePath = $this->getParameter('kernel.project_dir') . '/public/uploads/users/'.$user->getImage();
            $imageFile = new File($ImagePath);
            $user->setImageFile($imageFile);

            //si le code est coorect ,le compte sera verifié
            $user->setVerified(1);
            $em->flush();

            return $this->json(['status'=> 'success','message' => 'account verified successfully']);
        }

        return $this->json(['status'=> 'error','message' => 'incorrect code,try again']);








    }


}