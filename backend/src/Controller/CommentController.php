<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Project;
use App\Entity\User;
use App\Repository\CommentRepository;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/comments', name: 'api_comments_')]
class CommentController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CommentRepository $commentRepository,
        private ProjectRepository $projectRepository,
        private UserRepository $userRepository
    ) {}

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $comments = $this->commentRepository->findAll();
        return $this->json($comments, context: ['groups' => ['project:read']]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Comment $comment): JsonResponse
    {
        return $this->json($comment, context: ['groups' => ['project:read']]);
    }

    #[Route('/', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['content']) || !isset($data['project_id'])) {
                return $this->json(['error' => 'Content and project_id are required'], 400);
            }

            $project = $this->projectRepository->find($data['project_id']);
            if (!$project) {
                return $this->json(['error' => 'Project not found'], 404);
            }

            $comment = new Comment();
            $comment->setContent($data['content']);
            $comment->setProject($project);
            $comment->setCreatedAt(new \DateTimeImmutable());

            // Set author if provided
            if (isset($data['author_id'])) {
                $author = $this->userRepository->find($data['author_id']);
                if ($author) {
                    $comment->setAuthor($author);
                }
            }

            $this->entityManager->persist($comment);
            $this->entityManager->flush();

            return $this->json($comment, 201, context: ['groups' => ['project:read']]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Invalid data provided'], 400);
        }
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, Comment $comment): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (isset($data['content'])) {
                $comment->setContent($data['content']);
            }

            $this->entityManager->flush();

            return $this->json($comment, context: ['groups' => ['project:read']]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Invalid data provided'], 400);
        }
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Comment $comment): JsonResponse
    {
        $this->entityManager->remove($comment);
        $this->entityManager->flush();

        return $this->json(null, 204);
    }
}
