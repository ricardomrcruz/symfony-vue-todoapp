<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Task;



#[Route('/api/tasks', name: 'api_tasks_')]
class ToDoListController extends AbstractController
{   
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('', name:'', methods:['GET'])]
    public function getToDoList(): Response
    {
        $tasks = $this->entityManager -> getRepository(Task::class)->findAll();
        
        $response = $this->json($tasks, Response::HTTP_OK);
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        return $response;
    }

    #[Route('', name:'create', methods:['POST'])]
    public function createTask(Request $request, EntityManagerInterface $entityManagerInterface): JsonResponse
    {

        $data = json_decode((string) $request->getContent(), true);

        $task = new Task();
        $task->setName($data['name'] );
        $task->setDescription($data['description']);
        $task->setStatus(false);

        $entityManagerInterface->persist($task);
        $entityManagerInterface->flush();

        $tasks = $this->entityManager -> getRepository(Task::class)->findAll();
        return $this->json($tasks, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $task = $entityManager->getRepository(Task::class)->find($id);
        if (!$task) {
            return $this->json(['message' => 'Task not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $task->setName($data['name'] ?? $task->getName());
        $task->setDescription($data['description'] ?? $task->getDescription());
        if (isset($data['status'])) {
            $task->setStatus($data['status']);
        }

        $entityManager->flush();

        return $this->json($task);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $task = $entityManager->getRepository(Task::class)->find($id);
        if (!$task) {
            return $this->json(['message' => 'Task not found'], 404);
        }

        $entityManager->remove($task);
        $entityManager->flush();

        return $this->json(null, 204);
    }

    
}
