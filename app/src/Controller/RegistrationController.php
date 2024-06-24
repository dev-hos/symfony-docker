<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\RegistrationFormType;
use App\Repository\UsersRepository;
use App\Security\UsersAuthenticator;
use App\Service\JWTService;
use App\Service\SendEmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        Security $security,
        EntityManagerInterface $entityManager,
        SendEmailService $mail,
        JWTService $jwt
    ): Response {
        $user = new Users();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            // do anything else you need here, like send an email

            // générer le token
            $header = [
                'alg' => 'HS256',
                'typ' => 'JWT'
            ];
            $payload = [
                'user_id' => $user->getId(),
            ];

            $token = $jwt->generate($header, $payload, $this->getParameter('jwt_secret'));

            // envoyer l'email

            $mail->send(
                'no-replay@openblog.fr',
                $user->getEmail(),
                'Activation de votre compte sur le site openblog',
                'register',
                compact('user', 'token')
            );

            $this->addFlash('success', 'Utilisateur inscrit, veuillez cliquer sur le lien reçu pour confirmer votre adresse e-mail');
            return $security->login($user, UsersAuthenticator::class, 'main');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/verif/{token}', name: 'verify_user')]
    public function verifUser(
        $token,
        JWTService $jwt,
        UsersRepository $usersRepository,
        EntityManagerInterface $em
    ): Response {
        if ($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->check($token, $this->getParameter('jwt_secret'))) {
            $payload = $jwt->getPayload($token);
            $user = $usersRepository->find($payload['user_id']);
            if ($user && !$user->isVerified()) {
                $user->setVerified(true);
                $em->flush();
                $this->addFlash('success', 'Utilisateur activé');
                return $this->redirectToRoute('app_main');
            }
        }
        $this->addFlash('danger', 'Le token est invalide ou a expiré');
        return $this->redirectToRoute('app_login');
    }

    #[Route('/renvoi-verif', name: 'renvoi_verif')]
    public function renvoiVerif(SendEmailService $mail, JWTService $jwt): Response 
    {
        $user = $this->getUser();
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT'
        ];
        $payload = [
            'user_id' => $user->getId(),
        ];
        $token = $jwt->generate($header, $payload, $this->getParameter('jwt_secret'));
        $url = $this->generateUrl('verify_user', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

        $mail->send(
            'no-replay@openblog.fr',
            $user->getEmail(),
            'Activation de votre compte sur le site openblog',
            'renvoi_verif',
            compact('url', 'user')
        );
        $this->addFlash('success', 'Un email vous a été envoyé pour activer votre compte');
        return $this->redirectToRoute('app_main');
    }
}
