<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\UsuarioInputDTO;
use App\Entity\Usuario;
use App\Form\UsuarioType;
use App\Repository\UsuarioRepository;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminUserController extends AbstractController
{
    public function __construct(
        private UsuarioRepository $usuarioRepository,
    ) {
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/usuarios', name: 'app_admin_users_list', methods: ['GET'])]
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
    #[Route('/admin/usuarios/new', name: 'app_admin_users_new', methods: ['GET', 'POST'])]
    public function adminUsersNew(Request $request): Response
    {
        $userDTO = new UsuarioInputDTO();

        if ($request->isMethod('GET')) {
            $userForm = $this->createForm(UsuarioType::class, $userDTO);
            return $this->render('admin_area/edit-user.html.twig', compact('userForm'));
        }

        $userForm = $this->createForm(UsuarioType::class, $userDTO)
                         ->handleRequest($request);

        if (!$userForm->isSubmitted() || !$userForm->isValid()) {
            return $this->render('admin_area/edit-user.html.twig', compact('userForm'));
        }

        try {
            $this->usuarioRepository->storeFromDTO(dto: $userDTO, flush: true);
        } catch(UniqueConstraintViolationException) {
            $this->addFlash('warning', 'Usuário ou E-mail já cadastrados previamente, caso seja um usuário já existente, reative a conta');
            return $this->render('admin_area/edit-user.html.twig', compact('userForm'));
        } catch(Exception $e) {
            $this->addFlash('warning', $e->getMessage());
            return $this->render('admin_area/edit-user.html.twig', compact('userForm'));
        }

        return $this->redirectToRoute('app_admin_users_list');
    }


    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/usuarios/{usuario}', name: 'app_admin_users_edit', methods: ['GET', 'PATCH'])]
    public function adminUsersEdit(?Usuario $usuario, Request $request): Response
    {
        if (!$usuario) {
            return $this->redirectToRoute('app_admin_users_list');
        }

        $userDTO = new UsuarioInputDTO(
            $usuario->getUsuario(),
            $usuario->getNome(),
            $usuario->getEmail(),
            in_array('ROLE_ADMIN', $usuario->getRoles()) ? 'ROLE_ADMIN' : 'ROLE_USER',
            $usuario->getAtivo()
        );

        if ($request->isMethod('GET')) {
            $userForm = $this->createForm(UsuarioType::class, $userDTO, ['is_edit' => true]);
            return $this->render('admin_area/edit-user.html.twig', compact('userForm'));
        }

        $userForm = $this->createForm(UsuarioType::class, $userDTO, ['is_edit' => true])
                         ->handleRequest($request);

        if (!$userForm->isSubmitted() || !$userForm->isValid()) {
            return $this->render('admin_area/edit-user.html.twig', compact('userForm'));
        }

        try {
            $this->usuarioRepository->storeFromDTO(dto: $userDTO, usuario: $usuario, flush: true);
        } catch(UniqueConstraintViolationException) {
            $this->addFlash('warning', 'Usuário ou E-mail já cadastrados previamente, caso seja um usuário já existente, reative a conta');
            return $this->render('admin_area/edit-user.html.twig', compact('userForm'));
        } catch(Exception $e) {
            $this->addFlash('warning', $e->getMessage());
            return $this->render('admin_area/edit-user.html.twig', compact('userForm'));
        }

        return $this->redirectToRoute('app_admin_users_list');
    }

}
