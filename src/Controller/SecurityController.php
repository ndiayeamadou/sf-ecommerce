<?php

namespace App\Controller;

use App\Form\ResetPwdFormType;
use App\Form\ResetPwdRequestFormType;
use App\Repository\UserRepository;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

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

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername, 'error' => $error
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/forgot-pwd', name: 'forgotten_pwd')]
    public function forgottenPwd (
        EntityManagerInterface $emanager, Request $request, UserRepository $userRepository,
        TokenGeneratorInterface $tokenGeneratorInterface, SendMailService $mail
    )
    {
        $form = $this->createForm(ResetPwdRequestFormType::class);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            /** check user by his.her mail address */
            /** chercher les données (getData) dans le champ email de mon formulaire ($form) */
            $user = $userRepository->findOneByEmail($form->get('email')->getData());
            //dd($user);
            /** if user exists */
            if($user) {
                /** generate a reset token - un token de réinitialisation
                 * try another system (symfony token generator) - instead of using our service */
                $token = $tokenGeneratorInterface->generateToken();
                //dd($token);
                $user->setResetToken($token);
                $emanager->persist($user);
                $emanager->flush();

                /** generate a reset link for the password - based on creating a route above*/
                $url = $this->generateUrl('reset_pwd', ['token'=> $token], UrlGeneratorInterface::ABSOLUTE_URL);
                //dd($url);
                
                /** create les données du mail */
                $context = compact('url', 'user');

                /** sending mail */
                $mail->send(
                    'no-replay@e-commerce.org',
                    $user->getEmail(),
                    'Reset password',
                    'pwd_reset',
                    $context
                );

                $this->addFlash('success', 'Mail sent successfully !');
                return $this->redirectToRoute('app_login');
            }
            /** if user doesn't exist */
            $this->addFlash('danger', 'Sorry, an error occured.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_pwd_request.html.twig', [
            'requestPwdForm' => $form->createView()
        ]);
    }

    #[Route('/reset-pwd/{token}', name: 'reset_pwd')]
    public function resetPwd(
        string $token, Request $request, UserRepository $userRepository,
        EntityManagerInterface $emanager, UserPasswordHasherInterface $passwordHasher
    ): Response
    {
        /** check if we have token in DB */
        $user = $userRepository->findOneByResetToken($token);
        //dd($user);
        if($user) {
            $form = $this->createForm(ResetPwdFormType::class);
            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()) {
                /** clean the token */
                $user->setResetToken('');
                $user->setPassword(
                    $passwordHasher->hashPassword(
                        $user,
                        $form->get('password')->getData()
                    )
                );
                $emanager->persist($user);
                $emanager->flush();

                $this->addFlash('success', 'Password changed successfully !');
                return $this->redirectToRoute('app_login');
            }
            return $this->render('security/reset_pwd.html.twig', [
                'resetPwdForm' => $form->createView()
            ]);
        }
        $this->addFlash('danger', 'Invalid token.');
        return $this->redirectToRoute('app_login');
    }

}
