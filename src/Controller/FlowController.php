<?php

namespace App\Controller;

use App\Entity\Flow;
use App\Form\FlowType;
use App\Repository\FlowRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/flow')]
class FlowController extends AbstractController
{
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
        return $this->render('flow/show.html.twig', [
            'flow' => $flow,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_flow_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Flow $flow, FlowRepository $flowRepository): Response
    {
        $form = $this->createForm(FlowType::class, $flow);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $flowRepository->save($flow, true);

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
        if ($this->isCsrfTokenValid('delete' . $flow->getId(), $request->request->get('_token'))) {
            $flowRepository->remove($flow, true);
        }

        return $this->redirectToRoute('app_flow_index', [], Response::HTTP_SEE_OTHER);
    }
}