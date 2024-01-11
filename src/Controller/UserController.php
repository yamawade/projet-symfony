<?php
 
namespace App\Controller;
 
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
 
#[Route('/api', name: 'api_')]
class UserController extends AbstractController
{
    //Creation utilisateur
    #[Route('/register', name: 'register', methods: 'post')]
    public function index(ManagerRegistry $doctrine, Request $request, UserPasswordHasherInterface $passwordHasher, SerializerInterface $serializer): JsonResponse
    {
        $em = $doctrine->getManager();
        $decoded = json_decode($request->getContent());
        $email = $decoded->email;
        $name = $decoded->name;
        $plaintextPassword = $decoded->password;

        $user = new User();
        $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $plaintextPassword
        );
        $user->setPassword($hashedPassword);
        $user->setEmail($email);
        $user->setName($name);
        $em->persist($user);
        $em->flush();

        $cands = $serializer->serialize($user, 'json', ['groups' => 'user']);


        return new JsonResponse($cands, JsonResponse::HTTP_OK, [], true);
    }

    //Liste des utilisateurs
    #[Route('/listeUsers', name: 'get_allusers',methods:['GET'])]
    public function getAllUser(UserRepository $repository): Response
    {
        //$users= $this->user->findAll();
        $users= $repository->findAll();

        return $this->json($users);
    }

    //Deconnexion utilisateurs
    #[Route('/logout', name: 'app_logout', methods: ['POST'])]
    public function logout(): void
    {

        throw new \Exception('This should never be reached!');
    }
}