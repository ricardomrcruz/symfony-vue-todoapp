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

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ProjectRepository $projectRepository
    ) {}


    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $projects = $this->projectRepository->findAll();
        return $this->json($projects, context: ['groups' => ['project:read']]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Project $project): JsonResponse
    {
        if (!$project) {
            return $this->json(['error' => 'Project not found'], 404);
        }

        return $this->json($project, context: ['groups' => ['project:read']]);
    }


    #[Route('/', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $project = new Project();
        $project->setName($data['name']);
        $project->setDescription($data['description']);
        $project->setStatus($data['status'] ?? 'new');
        $project->setOwner($data['owner'] ?? null);


        if (isset($data['deadline'])) {
            $project->setDeadline(new \DateTime($data['deadline']));
        }

        $this->entityManager->persist($project);
        $this->entityManager->flush();

        return $this->json($project, 201, context: ['groups' => ['project:read']]);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, Project $project): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['name'])) {
            $project->setName($data['name']);
        }
        if (isset($data['description'])) {
            $project->setDescription($data['description']);
        }
        if (isset($data['status'])) {
            $project->setStatus($data['status']);
        }
        if (isset($data['owner'])) {
            $project->setOwner($data['owner']);
        }
        if (isset($data['deadline'])) {
            $project->setDeadline(new \DateTime($data['deadline']));
        }

        $this->entityManager->flush();

        return $this->json($project, context: ['groups' => ['project:read']]);
    }


    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Project $project): JsonResponse
    {
        $this->entityManager->remove($project);
        $this->entityManager->flush();

        return $this->json(null, 204);
    }
}
