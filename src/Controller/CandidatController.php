<?php

namespace App\Controller;

use App\Entity\Candidat;
use App\Entity\Formation;
use App\Entity\Candidature;
use App\Entity\User;
use OpenApi\Annotations as OA;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Annotation\Groups;
 
#[Route("/api",name:"api_")]
class CandidatController extends AbstractController
{
    #[Route("/postuler", name:"postuler", methods:["POST"])]
    public function postuler(Request $request, EntityManagerInterface $entityManager): Response
    {
        $candidat = $this->getUser();
        $data = json_decode($request->getContent(), true);
    
        // $user = $this->getUser(); 
        $formationId = $data['formation_id'];

        $candidatureExistante = $entityManager->getRepository(User::class)->findOneBy([
            // 'user' => $user,
            'formation' => $formationId
        ]);
    
        if ($candidatureExistante) {
            return $this->json(['message' => 'Vous avez déjà postulé à cette formation'], Response::HTTP_BAD_REQUEST);
        }


        $formation = $entityManager->getRepository(Formation::class)->findOneBy(['nomFormation' => $data['nomFormation']]);

        if (!$formation) {
            return $this->json(['message' => 'Formation non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $candidature = new Candidat();
        $entityManager->persist($candidature);
        $entityManager->flush();

        return $this->json(['message' => 'Candidature enregistrée'], Response::HTTP_OK);
    }

    #[Route('/candidatures/acceptees', name: 'app_api_candidatures_acceptees', methods: ['GET'])]
    public function getCandidaturesAcceptees(EntityManagerInterface $entityManager)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $candidaturesAcceptees = $entityManager->getRepository(Candidat::class)->findBy(['status' => 'accepter']);

        $formattedCandidatures = $this->formatCandidatures($candidaturesAcceptees);

        return $this->json($formattedCandidatures);
    }

    public function formatCandidatures(array $candidatures): array
    {
        $formattedCandidatures = [];
        foreach ($candidatures as $candidature) {
            $formattedCandidatures[] = [
                'id' => $candidature->getId(),
                'etat' => $candidature->getEtat(),
            
            ];
        }
        return $formattedCandidatures;
    }

    #[Route('/candidatures/refusees', name: 'app_api_candidatures_refusees', methods: ['GET'])]
    public function getCandidaturesRefusees(EntityManagerInterface $entityManager)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $candidaturesRefusees = $entityManager->getRepository(Candidat::class)->findBy(['etat' => 'refuser']);

        
        $formattedCandidatures = $this->formatCandidatures($candidaturesRefusees);

        return $this->json($formattedCandidatures);
    }

}


