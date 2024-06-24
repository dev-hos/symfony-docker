<?php

namespace App\Controller;

use App\Form\ResetPasswordFormType;
use App\Service\JWTService;
use App\Service\SendEmailService;
use App\Repository\UsersRepository;
use App\Form\ResetPasswordRequestType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/mot-de-passe-oublie', name: 'forgotten_password')]
    public function forgottenPassword(
        Request $request,
        UsersRepository $usersRepository,
        EntityManagerInterface $em,
        JWTService $jwt,
        SendEmailService $email,


    ): Response {
        $form = $this->createForm(ResetPasswordRequestType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $usersRepository->findOneBy(['email' => $form->get('email')->getData()]);
            if ($user) {
                $header = [
                    'alg' => 'HS256',
                    'typ' => 'JWT'
                ];
                $payload = [
                    'user_id' => $user->getId(),
                ];

                $token = $jwt->generate($header, $payload, $this->getParameter('jwt_secret'));
                $url = $this->generateUrl('forgotten_password_reset', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

                $email->send(
                    'no-replay@openblog.fr',
                    $user->getEmail(),
                    'Réinitialisation de votre mot de passe sur le site openblog',
                    'reset_password',
                    compact('user', 'url')
                );
                $this->addFlash('success', 'Un email vous a été envoyé pour réinitialiser votre mot de passe');
                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('security/reset_password_request.html.twig', [
            'requestPassForm' => $form
        ]);
    }

    #[Route(path: '/mot-de-passe-oublie/{token}', name: 'forgotten_password_reset')]
    public function forgottenPasswordReset(
        $token,
        JWTService $jwt,
        UsersRepository $usersRepository,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        Request $request
    ): Response {
        if ($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->check($token, $this->getParameter('jwt_secret'))) {
            $payload = $jwt->getPayload($token);
            $user = $usersRepository->find($payload['user_id']);
            if ($user) {

                $form = $this->createForm(ResetPasswordFormType::class);
                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {

                    $user->setPassword($hasher->hashPassword($user, $form->get('password')->getData()));
                    $em->flush();
                    $this->addFlash('success', 'Votre mot de passe a bien été modifié');
                    return $this->redirectToRoute('app_login');
                }
            }
            return $this->render('security/reset_password.html.twig', [
                'resetPassForm' => $form
            ]);
        }
        $this->addFlash('danger', 'Le token est invalide ou a expiré');
        return $this->redirectToRoute('app_login');
    }
}
