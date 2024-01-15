<?php
 
namespace App\Controller;
 
use App\Entity\Formation;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
 
#[Route('/api', name: 'api_')]
class FormationController extends AbstractController
{
    #[Route('/Formations', name: 'Formation_index', methods:['get'] )]
    public function index(ManagerRegistry $doctrine): JsonResponse
    {
        $products = $doctrine
            ->getRepository(Formation::class)
            ->findAll();
   
        $data = [];
   
        foreach ($products as $product) {
           $data[] = [
               'id' => $product->getId(),
               'name' => $product->getName(),
               'description' => $product->getDescription(),
               'duree' => $product->getDuree(),
           ];
        }
   
        return $this->json($data);
    }
 
    #[Route('/Formations', name: 'Formation_create', methods:['post'] )]
    public function create(ManagerRegistry $doctrine, Request $request,SerializerInterface $serializer,ValidatorInterface $validator,EntityManagerInterface $entityManager): JsonResponse
    {
        //dd($request);
        $data = $request->getContent();
        $formation = $serializer->deserialize($data, Formation::class, 'json');        
        $errors = $validator->validate($formation);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $violation) {
                $errorMessages[$violation->getPropertyPath()] = $violation->getMessage();
            }
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }
    
        $entityManager->persist($formation);
        $entityManager->flush();
    
        return $this->json($formation, Response::HTTP_CREATED);
    }

    #[Route('/Formations/{id}', name: 'Formation_show', methods:['get'] )]
    public function show(ManagerRegistry $doctrine, int $id): JsonResponse
    {
        $Formation = $doctrine->getRepository(Formation::class)->find($id);
   
        if (!$Formation) {
   
            return $this->json('No Formation found for id ' . $id, 404);
        }
   
        $data =  [
            'id' => $Formation->getId(),
            'name' => $Formation->getName(),
            'description' => $Formation->getDescription(),
            'duree' => $Formation->getDuree(),
        ];
           
        return $this->json($data);
    }

    #[Route('/Formations/{id}', name: 'Formation_update', methods:['put', 'patch'] )]
    public function update(ManagerRegistry $doctrine, Request $request, int $id): JsonResponse
    {
        $entityManager = $doctrine->getManager();
        $Formation = $entityManager->getRepository(Formation::class)->find($id);
   
        if (!$Formation) {
            return $this->json('No Formation found for id' . $id, 404);
        }
   
        $Formation->setName($request->request->get('name'));
        $Formation->setDescription($request->request->get('description'));
        $Formation->setDuree($request->request->get('duree'));
        $entityManager->flush();
   
        $data =  [
            'id' => $Formation->getId(),
            'name' => $Formation->getName(),
            'description' => $Formation->getDescription(),
            'duree' => $Formation->getDuree(),
        ];
           
        return $this->json($data);
    }

    #[Route('/Formations/{id}', name: 'Formation_delete', methods:['delete'] )]
    public function delete(ManagerRegistry $doctrine, int $id): JsonResponse
    {
        $entityManager = $doctrine->getManager();
        $Formation = $entityManager->getRepository(Formation::class)->find($id);
   
        if (!$Formation) {
            return $this->json('No Formation found for id' . $id, 404);
        }
   
        $entityManager->remove($Formation);
        $entityManager->flush();
   
        return $this->json('Deleted a Formation successfully with id ' . $id);
    }
}