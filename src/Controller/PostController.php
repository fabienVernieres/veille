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
    /**
     * Ajoute un nouveau post aux favoris.
     *
     * @param  EntityManagerInterface $entityManager
     * @param  PostRepository $postRepository
     * @return JsonResponse
     */
    public function new(EntityManagerInterface $entityManager, PostRepository $postRepository): JsonResponse
    {
        // Crée un nouvel objet Post.
        $post = new Post();

        // Récupère le body de la requête et le convertit en tableau.
        $request = new Request($_POST);
        $request = $request->toArray();

        $post->setUser($this->getUser());
        $post->setTitle($request['title']);
        $post->setDescription($request['description']);
        $post->setLink($request['link']);
        $post->setCreatedAt(new DateTimeImmutable());

        try {
            // Si le post n'est pas déjà sauvegardé, enregistre le en favori.
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
    /**
     * Affiche les favoris de l'utilisateur.
     *
     * @param  PostRepository $postRepository
     * @return Response
     */
    public function index(PostRepository $postRepository): Response
    {
        $posts = $postRepository->findBy(['user' => $this->getUser()], ['id' => 'DESC']);
        return $this->render('post/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/delete', name: 'app_post_delete', methods: ['POST'])]
    /**
     * Supprime un post des favoris.
     *
     * @param  EntityManagerInterface $entityManager
     * @param  PostRepository $postRepository
     * @return JsonResponse
     */
    public function delete(EntityManagerInterface $entityManager, PostRepository $postRepository): JsonResponse
    {
        // Crée un nouvel objet Post.
        $post = new Post();

        // Récupère le post dans le body de la requête.
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