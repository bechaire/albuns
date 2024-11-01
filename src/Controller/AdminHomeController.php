<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\AlbumRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminHomeController extends AbstractController
{
    public function __construct(
        private AlbumRepository $albumRepository,
    ) {
    }

    //HOME - listagem de álbums
    #[Route('/admin', name: 'app_admin_home')]
    public function adminHomeArea(): Response
    {
        return $this->render('admin_area/list-album.html.twig');
    }

    //HOME - dados da listagem de álbuns
    #[Route('/admin/albuns', name: 'app_admin_albuns_list', methods: ['GET'])]
    public function adminAlbunsList(): JsonResponse
    {
        $albunsList = $this->albumRepository->albunsListWithPhotosCount();
        return $this->json(['data' => $albunsList]);
    }

}
