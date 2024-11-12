<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use App\Repository\ProjectRepository;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Task;
use App\Entity\Project;
use App\Entity\User;


#[Route('/api/tasks', name: 'api_tasks_')]
class TaskController extends AbstractController
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private TaskRepository $taskRepository,
        private ProjectRepository $projectRepository,
        private UserRepository $userRepository
    ) {}


    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $tasks = $this->taskRepository->findAll();
        return $this->json($tasks, context: ['groups' => ['project:read']]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Task $task): JsonResponse
    {
        if (!$task) {
            return $this->json(['error' => 'Task not found'], 404);
        }

        return $this->json($task, context: ['groups' => ['project:read']]);
    }


    #[Route('/', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {

        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['name']) || !isset($data['project_id'])) {
                return $this->json(['error' => 'Name and project id are required'], 400);
            }

            $project = $this->projectRepository->find($data['project']);
            if (!$project) {
                return $this->json(['error' => 'Project not found'], 404);
            }

            $task = new Task();
            $task->setName($data['name']);
            $task->setDescription($data['description'] ?? '');
            $task->setStatus($data['status'] ?? false);
            $task->setProject($project);


            if (isset($data['assignee_id'])) {
                $assignee = $this->userRepository->find($data['assignee_id']);
                if ($assignee) {
                    $task->setAssignee($assignee);
                }
            }

            $this->entityManager->persist($task);
            $this->entityManager->flush();

            return $this->json($task, 201, context: ['groups' => ['project:read']]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Invalid data provided'], 400);
        }
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, Task $task): JsonResponse
    {


        try {
            $data = json_decode($request->getContent(), true);

            if (isset($data['name'])) {
                $task->setName($data['name']);
            }
            if (isset($data['description'])) {
                $task->setDescription($data['description']);
            }
            if (isset($data['status'])) {
                $task->setStatus($data['status']);
            }

            if (isset($data['assignee_id'])) {
                $assignee = $this->userRepository->find($data['assignee_id']);
                if ($assignee) {
                    $task->setAssignee($assignee);
                }
            }

            $this->entityManager->flush();

            return $this->json($task, context: ['groups' => ['project:read']]);
        } catch (\Exception $e) {

            return $this->json(['error' => 'Invalid data provided'], 400);
        }
    }


    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Task $task): JsonResponse
    {
        $this->entityManager->remove($task);
        $this->entityManager->flush();

        return $this->json(null, 204);
    }
}
