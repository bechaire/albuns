<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\UsuarioRepository;
use App\Service\UserValidationService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminLoginController extends AbstractController
{
    public function __construct(
        private UsuarioRepository $usuarioRepository,
        private Security $security,
        private LoggerInterface $log
    ) {
    }

    #[Route('/admin/login', name: 'app_admin_login', methods: ['GET'])]
    public function loginForm(): Response
    {
        // $userLogged = $this->security->getUser();
        if ($this->container->get('security.authorization_checker')->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('app_admin_home', status: Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_area/login.html.twig');
    }

    #[Route('/admin/logout', name: 'app_admin_logout', methods: ['GET'])]
    public function adminLogout(): Response
    {
        $this->security->logout(false);
        $this->addFlash('info', 'Você deslogou');
        return $this->redirectToRoute('app_admin_login', status: Response::HTTP_SEE_OTHER);
    }

    #[Route('/admin/login', name: 'app_admin_login_post', methods: ['POST'])]
    public function loginValidation(Request $request, UserValidationService $validation): Response
    {
        $isProdMode = strtolower($this->getParameter('kernel.environment')) == 'prod';

        if ($isProdMode) {
            sleep(rand(1,3));
        }

        $usuarioInformado = $request->request->get('usuario');
        $senhaInformada = $request->request->get('senha');

        try {
            $usuario = $validation->validateUserPassword($usuarioInformado, $senhaInformada);
        } catch(\DomainException) {
            $this->addFlash('warning text-center', 'Usuário ou senha inválidos');
            return $this->redirectToRoute('app_admin_login', status: Response::HTTP_SEE_OTHER);
        }

        $usuario->setUltacesso(new \DateTimeImmutable());
        $this->usuarioRepository->add($usuario, true);

        $redirectResponse = $this->security->login($usuario, 'form_login');

        return $redirectResponse;

    }

}
