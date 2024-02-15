<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Usuario;
use App\Repository\AlbumRepository;
use App\Repository\UsuarioRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

class AdminHomeController extends AbstractController
{
    public function __construct(
        private AlbumRepository $albumRepository,
        private UsuarioRepository $usuarioRepository,
        private SluggerInterface $slugger,
    ) {
    }

    //HOME - listagem de álbums
    #[Route('/admin', name: 'app_admin_home')]
    public function adminHomeArea(): Response
    {
        return $this->render('admin_area/list-album.html.twig');
    }

    //HOME - dados da listagem de álbuns
    #[Route('/admin/albuns', name: 'app_admin_albuns', methods: ['GET'])]
    public function adminAlbunsList(): JsonResponse
    {
        $albunsList = $this->albumRepository->albunsListWithPhotosCount();
        return $this->json(['data' => $albunsList]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/usuarios', name: 'app_admin_usuarios', methods: ['GET'])]
    public function adminUsersList(Request $request): Response
    {
        $acceptJson = $request->getAcceptableContentTypes()[0] == 'application/json';
        if ($acceptJson) {
            $usersList = $this->usuarioRepository->findAllOrdered();
            return $this->json(['data' => $usersList]);
        }

        return $this->render('admin_area/list-user.html.twig');
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/usuarios/{usuario}', name: 'app_admin_usuarios_form', methods: ['GET'])]
    public function adminUsersForm(Usuario $usuario, Request $request): Response
    {
        return new Response('<body>'.$usuario->getNome().'</body>');
    }


    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/usuarios', name: 'app_admin_usuarios_post', methods: ['POST'])]
    public function adminUsersAdd(): Response
    {
        return new Response('<body>Acessou /admin/usuarios via POST</body>');
    }

    #[Route('/admin/album', name: 'app_admin_album', methods: ['GET'])]
    public function adminAlbumAdd(Album $album): Response
    {
        return new Response('<body>Acessou /admin/album via GET</body>');
    }

    #[Route('/admin/album/{album}', name: 'app_admin_album_edit', methods: ['GET'])]
    public function adminAlbumEdit(Album $album): Response
    {
        $titulo = strtolower((string) $this->slugger->slug($album->getTitulo()));
        $id = $album->getId();
        return new Response('<body>Acessou /admin/album/' . $id . '-' . $titulo . ' via GET</body>');
    }

    #[Route('/admin/album/{album}', name: 'app_admin_album_delete', methods: ['DELETE'])]
    public function adminAlbumDelete(Album $album): Response
    {
        return new Response('<body>Acessou /admin/album via GET</body>');
    }
}
