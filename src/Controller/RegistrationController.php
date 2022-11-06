<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\UsersAuthenticator;
use App\Service\JWTService;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request, UserPasswordHasherInterface $userPasswordHasher,
        UserAuthenticatorInterface $userAuthenticator, UsersAuthenticator $authenticator,
        EntityManagerInterface $entityManager, SendMailService $mail, JWTService $jwtService
    ): Response
    {
        $user = new User();
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

            /** TOKEN --- to create the link /\ remove it if you're sending simple message */
            /** generation JWT of the user */
            /** create the header */
            $header = [
                'typ' => 'JWT',
                'alg' => 'HS256'
            ];

            /** create payload */
            $payload = [
                'user_id' => $user->getId()
            ];

            /** generate token */
            $token = $jwtService->generate($header, $payload, $this->getParameter('app.jwtsecret'));

            // dd($token);
            /** END TOKEN */

            // we can now send mail using sendmailservice created
            $mail->send(
                'no-reply@website.net',
                $user->getEmail(),
                'Activation of your account to the ecommerce website',
                'registermail',
                [
                    'user' => $user, 'token' => $token
                ],
                // OR compact('user', 'token')
            );

            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verify/{token}', 'verify_user')]
    public function verifyUser($token, JWTService $jwt, UserRepository $userRepository, EntityManagerInterface $emanager): Response
    {
        //dd($token);
        //dd($jwt->isValid($token));
        //dd($jwt->getPayload($token));
        //dd($jwt->isExpired($token));
        //dd($jwt->check($token, $this->getParameter('app.jwtsecret')));

        /** check if token is valid, not expired, not modified */
        if($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->check($token, $this->getParameter('app.jwtsecret')))
        {
            /** récupérer le payload */
            $payload = $jwt->getPayload($token);
            /** récupérer le user du token */
            $user = $userRepository->find($payload['user_id']);
            /** check if user exists and didn't activate his account */
            if($user && !$user->getIsVerified()) {
                $user->setIsVerified(true);
                $emanager->flush($user);
                $this->addFlash('success', 'User activated !');
                return $this->redirectToRoute('profile_index');
            }
        }

        /** if there is an issue in the token */
        $this->addFlash('danger', 'Le token est invalide ou a expiré.');
        return $this->redirectToRoute('app_login');
    }

    #[Route('/resendverif', 'resend_verif')]
    public function resendVerif(JWTService $jwtService, SendMailService $mail, UserRepository $userRepository): Response
    {
        $user = $this->getUser();

        if(!$user) {
            $this->addFlash('danger', 'You must be connected to view this page.');
            return $this->redirectToRoute('app_login');
        }

        if($user->getIsVerified()) {
            $this->addFlash('warning', 'This user is already activated.');
            return $this->redirectToRoute('profile_index');
        }

        /** TOKEN */
        /** generation JWT of the user */
        /** create the header */
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256'
        ];

        /** create payload */
        $payload = [
            'user_id' => $user->getId()
        ];

        /** generate token */
        $token = $jwtService->generate($header, $payload, $this->getParameter('app.jwtsecret'));

        // dd($token);
        /** END TOKEN */

        // we can now send mail using sendmailservice created
        $mail->send(
            'no-reply@website.net',
            $user->getEmail(),
            'Activation of your account to the ecommerce website',
            'registermail',
            compact('user', 'token')
        );

        /** return the response */
        $this->addFlash('success', 'Verification email sent.');
        return $this->redirectToRoute('profile_index');

    }

}
