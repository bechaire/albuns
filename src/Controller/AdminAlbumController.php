<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\AlbumInputDTO;
use App\Entity\Album;
use App\Form\AlbumType;
use App\Repository\AlbumRepository;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class AdminAlbumController extends AbstractController
{
    public function __construct(
        private AlbumRepository $albumRepository,
    ) {
    }

    #[Route('/admin/albuns/new', name: 'app_admin_albuns_add', methods: ['GET', 'POST'])]
    public function adminAlbumAdd(Request $request, UserInterface $user): Response
    {
        $albumDTO = new AlbumInputDTO();

        if ($request->isMethod('GET')) {
            $albumForm = $this->createForm(AlbumType::class, $albumDTO);
            return $this->render('admin_area/edit-album.html.twig', compact('albumForm'));
        }

        $albumForm = $this->createForm(AlbumType::class, $albumDTO)
                         ->handleRequest($request);

        if (!$albumForm->isSubmitted() || !$albumForm->isValid()) {
            return $this->render('admin_area/edit-album.html.twig', compact('albumForm'));
        }

        try {
            $this->albumRepository->storeFromDTO(dto: $albumDTO, flush: true);
        } catch(UniqueConstraintViolationException) {
            $this->addFlash('warning', 'Usuário ou E-mail já cadastrados previamente, caso seja um usuário já existente, reative a conta');
            return $this->render('admin_area/edit-album.html.twig', compact('albumForm'));
        } catch(Exception $e) {
            $this->addFlash('warning', $e->getMessage());
            return $this->render('admin_area/edit-album.html.twig', compact('albumForm'));
        }

        return $this->redirectToRoute('app_admin_home');
    }

    #[Route('/admin/albuns/{album}', name: 'app_admin_albuns_edit', methods: ['GET', 'PATCH'])]
    public function adminAlbumEdit(?Album $album, Request $request): Response
    {

        if (!$album) {
            return $this->redirectToRoute('app_admin_home');
        }

        $albumDTO = new AlbumInputDTO(
            $album->getInstituicao(),
            $album->getTitulo(),
            $album->getData(),
            $album->getLocal(),
            $album->getStatus(),
            $album->getAddtag(),
            $album->getUsuario()->getNome(),
            $album->getCreated(),
        );

        if ($request->isMethod('GET')) {
            $albumForm = $this->createForm(AlbumType::class, $albumDTO, ['is_edit'=>true]);
            return $this->render('admin_area/edit-album.html.twig', compact('albumForm'));
        }

        $albumForm = $this->createForm(AlbumType::class, $albumDTO, ['is_edit'=>true])
                         ->handleRequest($request);

        if (!$albumForm->isSubmitted() || !$albumForm->isValid()) {
            return $this->render('admin_area/edit-album.html.twig', compact('albumForm'));
        }

        try {
            $this->albumRepository->storeFromDTO(dto: $albumDTO, album: $album, flush: true);
        } catch(UniqueConstraintViolationException) {
            $this->addFlash('warning', 'Usuário ou E-mail já cadastrados previamente, caso seja um usuário já existente, reative a conta');
            return $this->render('admin_area/edit-album.html.twig', compact('albumForm'));
        } catch(Exception $e) {
            $this->addFlash('warning', $e->getMessage());
            return $this->render('admin_area/edit-album.html.twig', compact('albumForm'));
        }

        return $this->redirectToRoute('app_admin_home');
    }

    #[Route('/admin/albuns/{album}', name: 'app_admin_albuns_delete', methods: ['DELETE'])]
    public function adminAlbumDelete(Album $album): Response
    {
        return new Response('<body>Acessou /admin/album via GET</body>');
    }
}
