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
        return $this->json($projects, context: ['groups' => ['projects:read']]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Project $project): JsonResponse
    {
        return $this->json($project, context: ['groups' => ['project:read']]);
    }


    #[Route('/', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $project = new Project();
        $project->setName($data['name']);
        $project->setDescription($data['description']);
        $project->setStatus($data['status' ?? 'new']);
        $project->setOwner($data['owner'] ?? null);


        if (isset($data['deadline'])) {
            $project->setDeadline(new \DateTime($data['deadline']));
        }

        $this->entityManager->persist($project);
        $this->entityManager->flush();

        return $this->json($project, 201, context: ['groups' => ['project:read']]);
    }
}
