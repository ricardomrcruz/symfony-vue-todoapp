<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;


#[Route('/api/projects', name: 'api_projects')]
class ProjectController extends AbstractController
{

    private $entityManager;
    private $projectRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ProjectRepository $projectRepository
    ) {
        $this->entityManager = $entityManager;
        $this->projectRepository = $projectRepository;
    }



    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $projects = $this->projectRepository->findAll();

        $data = array_map(function ($project) {
            return [
                'id' => $project->getId(),
                'name' => $project->getName(),
                'description' => $project->getDescription(),
                'status' => $project->getStatus(),
                'deadline' => $project->getDeadline() ? $project->getDeadline()->format('Y-m-d H:i:s') : null,
                'owner' => $project->getOwner(),
                'members' => $project->getMembers()->map(fn($member) => [
                    'id' => $member->getId(),
                    'email' => $member->getEmail(),
                    'firstName' => $member->getFirstName(),
                    'lastName' => $member->getLastName(),
                ])->toArray()
            ];
        }, $projects);

        return $this->json($data);
    }
}
