<?php

namespace App\Controller;

use App\Entity\Flow;
use App\Form\FlowType;
use App\Repository\FlowRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/flow')]
class FlowController extends AbstractController
{
    private Security $security;
    private FilesystemAdapter $cache;
    private string $cacheName;

    public function __construct(Security $security)
    {
        /** Définition du nom du cache. */
        $this->cache = new FilesystemAdapter();
        $this->security = $security;
        $this->cacheName = 'cache-' . $this->security->getUser()->getUserIdentifier();
        $this->cacheName = str_replace('@', '', $this->cacheName);
    }

    #[Route('/', name: 'app_flow_index', methods: ['GET'])]
    public function index(FlowRepository $flowRepository): Response
    {
        return $this->render('flow/index.html.twig', [
            'flows' => $flowRepository->findBy(['User' => $this->getUser()]),
        ]);
    }

    #[Route('/new', name: 'app_flow_new', methods: ['GET', 'POST'])]
    public function new(Request $request, FlowRepository $flowRepository): Response
    {
        $flow = new Flow();
        $form = $this->createForm(FlowType::class, $flow);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $flow->setUser($this->getUser());
            $flowRepository->save($flow, true);

            /** Suppression du cache pour mise à jour. */
            $this->cache->delete($this->cacheName);

            return $this->redirectToRoute('app_flow_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('flow/new.html.twig', [
            'flow' => $flow,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_flow_show', methods: ['GET'])]
    public function show(Flow $flow): Response
    {
        // Vérifie si l'utilisateur à les droits pour voir le flux.
        $this->denyAccessUnlessGranted('POST_VIEW', $flow);

        return $this->render('flow/show.html.twig', [
            'flow' => $flow,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_flow_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Flow $flow, FlowRepository $flowRepository): Response
    {
        // Vérifie si l'utilisateur à les droits pour éditer le flux.
        $this->denyAccessUnlessGranted('POST_EDIT', $flow);

        $form = $this->createForm(FlowType::class, $flow);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $flowRepository->save($flow, true);

            /** Suppression du cache pour mise à jour. */
            $this->cache->delete($this->cacheName);

            return $this->redirectToRoute('app_flow_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('flow/edit.html.twig', [
            'flow' => $flow,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_flow_delete', methods: ['POST'])]
    public function delete(Request $request, Flow $flow, FlowRepository $flowRepository): Response
    {
        // Vérifie si l'utilisateur à les droits pour supprimer le flux.
        $this->denyAccessUnlessGranted('POST_DELETE', $flow);

        if ($this->isCsrfTokenValid('delete' . $flow->getId(), $request->request->get('_token'))) {
            $flowRepository->remove($flow, true);

            /** Suppression du cache pour mise à jour. */
            $this->cache->delete($this->cacheName);
        }

        return $this->redirectToRoute('app_flow_index', [], Response::HTTP_SEE_OTHER);
    }
}