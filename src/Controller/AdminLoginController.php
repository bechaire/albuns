<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\UsuarioRepository;
use App\Service\LdapUserValidationService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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
        return $this->render('admin_area/login.html.twig');
    }

    #[Route('/admin/login', name: 'app_admin_login_post', methods: ['POST'])]
    public function loginValidation(Request $request, LdapUserValidationService $ldap): Response
    {
        $isDevMode = strtolower($this->getParameter('kernel.environment')) == 'dev';

        if (!$isDevMode) {
            sleep(rand(1,3));
        }

        $usuarioInformado = $request->request->get('usuario');
        $senhaInformada = $request->request->get('senha');

        $usuarioOuSenhaEmBranco = empty($usuarioInformado) || empty($senhaInformada);

        if ($usuarioOuSenhaEmBranco) {
            $this->addFlash('warning text-center', 'Usuário ou senha inválidos');
            return $this->redirectToRoute('app_admin_login', status: Response::HTTP_SEE_OTHER);
        }

        $usuario = $this->usuarioRepository->findOneBy([
            'usuario' => $request->request->get('usuario'),
            'ativo' => 'S'
        ]);

        if (!$usuario) {
            $this->addFlash('warning text-center', 'Usuário ou senha inválidos');
            $this->log->warning('Tentativa de logon com usuário não cadastrado previamente ou desativado', [$request->request->get('usuario')]);
            return $this->redirectToRoute('app_admin_login', status: Response::HTTP_SEE_OTHER);
        }

        $usuarioLocalizado = $usuario->getUsuario();
        $usuarioValido = $ldap->isValid($usuarioLocalizado, $senhaInformada);

        if (!$usuarioValido) {
            $this->addFlash('warning text-center', 'Usuário ou senha inválidos');
            return $this->redirectToRoute('app_admin_login', status: Response::HTTP_SEE_OTHER);
        }

        $redirectResponse = $this->security->login($usuario, 'form_login');

        // return $this->redirectToRoute('app_admin', status: Response::HTTP_SEE_OTHER);
        return $redirectResponse;

    }

    #[Route('/admin/logout', name: 'app_admin_logout')]
    public function adminLogout(): Response
    {
        $this->security->logout(false);
        $this->addFlash('info', 'Você deslogou');
        return $this->redirectToRoute('app_admin_login', status: Response::HTTP_SEE_OTHER);
    }

    #[Route('/admin', name: 'app_admin')]
    public function adminArea(): Response
    {
        return new Response('<body>Acessou /admin</body>');
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/usuarios', name: 'app_admin_usuarios', methods: ['GET'])]
    public function adminUsersList(): Response
    {
        return new Response('<body>Acessou /admin/usuarios via GET</body>');
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/usuarios', name: 'app_admin_usuarios_post', methods: ['POST'])]
    public function adminUsersAdd(): Response
    {
        return new Response('<body>Acessou /admin/usuarios via POST</body>');
    }
}
