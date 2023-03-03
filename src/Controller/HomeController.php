<?php

namespace App\Controller;

use Laminas\Feed\Reader\Reader;
use App\Repository\FlowRepository;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(FlowRepository $flowRepository): Response
    {
        /** @var array Tableau regroupant les données des différents flux. */
        $arrayData = [];

        if ($this->getUser()) {
            /** Définition du nom du cache. */
            $cache = new FilesystemAdapter();
            $cacheName = 'cache-' . $this->getUser()->getUserIdentifier();
            $cacheName = str_replace('@', '', $cacheName);

            /** @var array Liste des flux de l'utilisateur. */
            $flows = $flowRepository->findBy(['User' => $this->getUser()]);

            if ($flows) {
                /** Pour chaque flux les données sont stockées dans $arrayData. */
                $arrayData = $cache->get($cacheName, function (ItemInterface $item) use ($flows) {
                    $item->expiresAfter(3600);

                    foreach ($flows as $flow) {
                        $feed = Reader::import($flow->getUrl());
                        $data = [
                            'title'        => $feed->getTitle(),
                            'link'         => $feed->getLink(),
                            'dateModified' => $feed->getDateModified(),
                            'description'  => $feed->getDescription(),
                            'language'     => $feed->getLanguage(),
                            'entries'      => [],
                        ];

                        foreach ($feed as $entry) {
                            $edata = [
                                'title'        => $entry->getTitle(),
                                'description'  => $entry->getDescription(),
                                'dateModified' => $entry->getDateModified(),
                                'authors'      => $entry->getAuthors(),
                                'link'         => $entry->getLink(),
                                'content'      => $entry->getContent(),
                            ];
                            $data['entries'][] = $edata;
                        }
                        $arrayData[] = $data;
                    }

                    return $arrayData;
                });
            }
        }

        return $this->render('home/index.html.twig', [
            'arrayData' => $arrayData,
        ]);
    }
}