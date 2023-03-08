<?php

namespace App\Controller;

use Exception;
use App\Entity\Post;
use DateTimeImmutable;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/post')]
class PostController extends AbstractController
{
    #[Route('/new', name: 'app_post_new', methods: ['POST'])]
    public function new(EntityManagerInterface $entityManager, PostRepository $postRepository): JsonResponse
    {
        // Create a new user object
        $post = new Post();

        $request = new Request($_POST);
        $request = $request->toArray();

        $post->setUser($this->getUser());
        $post->setTitle($request['title']);
        $post->setDescription($request['description']);
        $post->setLink($request['link']);
        $post->setCreatedAt(new DateTimeImmutable());

        try {
            if (!$postRepository->findBy(['user' => $post->getUser(), 'link' => $post->getLink()])) {
                $entityManager->persist($post);
                $entityManager->flush();
                return new JsonResponse(null, 201, [], false);
            }
        } catch (Exception $e) {
            return new JsonResponse(null, 200, [], false);
        }
    }

    #[Route('/', name: 'app_post', methods: ['GET'])]
    public function index(PostRepository $postRepository): Response
    {
        $posts = $postRepository->findBy(['user' => $this->getUser()], ['id' => 'DESC']);
        return $this->render('post/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/delete', name: 'app_post_delete', methods: ['POST'])]
    public function delete(EntityManagerInterface $entityManager, PostRepository $postRepository): JsonResponse
    {
        // Crée un nouvel objet Post.
        $post = new Post();

        $request = new Request($_POST);
        $request = $request->toArray();
        $post = $postRepository->find($request['id']);

        // Vérifie si l'utilisateur à les droits pour supprimer le favori.
        $this->denyAccessUnlessGranted('POST_DELETE', $post);

        try {
            $entityManager->remove($post);
            $entityManager->flush();

            return new JsonResponse(null, 204, [], false);
        } catch (Exception $e) {
            return new JsonResponse(null, 200, [], false);
        }
    }
}
