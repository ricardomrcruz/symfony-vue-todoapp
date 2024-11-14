<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserRepository;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;





#[Route('/api/users', name: 'api_users_')]
class UserController extends AbstractController
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
    ) {}


    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $users = $this->userRepository->findAll();
        return $this->json($users, context: ['groups' => ['project:read']]);
    }


    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(User $user): JsonResponse
    {

        return $this->json($user, context: ['groups' => ['project:read']]);
    }


    #[Route('/', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {

        try {

            $data = json_decode($request->getContent(), true);

            if (!isset($data['email']) || !isset($data['password'])) {
                return $this->json(['error' => 'Email and password are required'], 400);
            }

            $existingUser = $this->userRepository->findOneBy(['email' => $data['email']]);
            if ($existingUser) {
                return $this->json(['error' => 'Email already exists'], 400);
            }

            $user = new User();
            $user->setEmail($data['email']);

            // hash pass
            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                $data['password']
            );
            $user->setPassword($hashedPassword);

            $user->setPassword($hashedPassword);

            $user->setFirstName($data['firstName'] ?? null);
            $user->setLastName($data['lastName'] ?? null);
            $user->setRoles($data['roles'] ?? ['ROLE_USER']);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return $this->json($user, 201, context: ['groups' => ['project:read']]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Invalid data submitted'], 400);
        }
    }


    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, User $user): JsonResponse
    {
        try {

            $data = json_decode($request->getContent(), true);

            if (isset($data['email'])) {
                // checks if email already exists
                $existingUser = $this->userRepository->findOneBy(['email' => $data['email']]);
                if ($existingUser && $existingUser->getId() !== $user->getId()) {
                    return $this->json(['error' => 'Email already exists'], 400);
                }
                $user->setEmail($data['email']);
            }

            if (isset($data['password'])) {
                $hashedPassword = $this->passwordHasher->hashPassword(
                    $user,
                    $data['password']
                );
                $user->setPassword($hashedPassword);
            }

            if (isset($data['firstName'])) {
                $user->setFirstName($data['firstName']);
            }

            if (isset($data['lastName'])) {
                $user->setLastName($data['lastName']);
            }

            if (isset($data['roles'])) {
                $user->setRoles($data['roles']);
            }

            $this->entityManager->flush();

            return $this->json($user, context: ['groups' => ['project:read']]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Invalid data provided'], 400);
        }
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(User $user): JsonResponse
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return $this->json(null, 204);
    }

    #[Route('/{id}/projects', name: 'user_projects_', methods: ['GET'])]
    public function getUserProjects(User $user): JsonResponse
    {
        return $this->json($user->getProjects(), context: ['groups' => ['project:read']]);
    }

    #[Route('/{id}/tasks', name: 'user_tasks_', methods: ['GET'])]
    public function getUserTasks(User $user): JsonResponse
    {
        $tasks = $this->entityManager
            ->getRepository('App:Task')
            ->findBy(['assignee' => $user]);

        return $this->json($tasks, context: ['groups' => ['project:read']]);
    }
}
