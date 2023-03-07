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

        $em->persist($user);
        $em->flush();

        return $this->json([
            'status'=> 'success',
            'message' => 'Registered Successfully'
        ]);
    }



}
