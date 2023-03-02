<?php

namespace App\Controller;

use Laminas\Feed\Reader\Reader;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $feed = Reader::import('http://feeds.feedburner.com/Grafikart');
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

        return $this->render('home/index.html.twig', [
            'data' => $data,

        ]);
    }
}