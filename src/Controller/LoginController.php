<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function index(AuthenticationUtils $authenticationUtils): Response
    {
        return $this->render('login/index.html.twig',[
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'username_label' => 'Email',
            'password_label' => 'Password',
            'sign_in_label' => 'Log in',
            'forgot_password_enabled' => false,
            'csrf_token_intention' => 'authenticate',
            'username_parameter' => 'email',
            'target_path' => $this->generateUrl('app_login'),
            'translation_domain' => 'admin',
            'password_parameter' => 'password',]);
    }
}
