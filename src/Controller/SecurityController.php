<?php

namespace App\Controller;


use App\Entity\Uai;
use App\Entity\User;
use App\Form\ResettingType;
use App\Form\UserRegistrationFormType;
use App\Service\Mailer;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use VictorPrdh\RecaptchaBundle\Form\ReCaptchaType;

class SecurityController extends AbstractController
{
    private RequestStack $requestStack;
    private ManagerRegistry $doctrine;

    public function __construct(RequestStack $requestStack, ManagerRegistry $doctrine)
    {
        $this->requestStack = $requestStack;
        $this->doctrine = $doctrine;
    }

    #[Route("/security/login", name: "login")]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    /**
     * @throws Exception
     */
    #[Route("/logout", name: "logout")]
    public function logout()
    {
        throw new Exception('Sera intercepté avant d\'en arriver là !');
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route("/register", name: "register")]
    public function register(Request $request, UserPasswordHasherInterface $passwordEncoder, Mailer $mailer, ManagerRegistry $doctrine, TokenGeneratorInterface $tokenGenerator): Response
    {

        $uaiRepository = $doctrine->getRepository(Uai::class);

        // création du formulaire
        $user = new User();
        // instancie le formulaire avec les contraintes par défaut, + la contrainte registration pour que la saisie du mot de passe soit obligatoire
        $form = $this->createForm(UserRegistrationFormType::class, $user, [
            'validation_groups' => array('User', 'registration'),
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $uai = $form->get('uai')->getData();

            if ($uaiRepository->findOneBy(['uai' => $uai]) == null) {
                $request->getSession()
                    ->getFlashBag()
                    ->add('alert', 'Cette UAI n\'est pas valide !');

                return $this->redirectToRoute('register');
            }
            $uaiId = $uaiRepository->findBy(['uai' => $uai]);
            $password = $passwordEncoder->hashPassword($user, $user->getPlainPassword());
            $user->setPassword($password);
            $user->setUai($uai);
            $user->setUaiId($uaiId[0]);

            $user->setIsActive(0);//inactive l'User en attente de la vérification du mail
            $user->setToken($tokenGenerator->generateToken());
            // enregistrement de la date de création du token
            $user->setPasswordRequestedAt(new DateTime());
            $user->setCreatedAt(new DateTime());
            $nom = $form->get('nom')->getData();
            $nom = strtoupper($nom);
            $user->setNom($nom);
            $prenom = $form->get('prenom')->getData();
            $prenom = ucfirst(strtolower($prenom));
            $user->setPrenom($prenom);
            $adresse = $form->get('adresse')->getData();
            $user->setAdresse($adresse);
            $ville = $form->get('ville')->getData();
            $user->setVille($ville);
            $code = $form->get('code')->getData();
            $user->setCode($code);
            $phone = $form->get('phone')->getData();
            $user->setPhone($phone);
            // Enregistre le membre en base
            $em = $doctrine->getManager();
            $em->persist($user);
            $em->flush();
            $mailer->sendVerifEmail($user);
            //$request->getSession()->getFlashBag()->add('success', "Un mail va vous être envoyé afin que vous puissiez finaliser votre inscription. Le lien que vous recevrez sera valide 24h.");
            $this->requestStack->getSession()->set('nluser', true);
            return $this->redirectToRoute("core_home");

        }
        return $this->render('register/register.html.twig',
            array('form' => $form->createView())
        );
    }

    #[Route("/verif_mail/{id}/{token}", name: "verif_mail")]
    public function verifMail(User $user, Request $request, Mailer $mailer, ManagerRegistry $doctrine, string $token): RedirectResponse
    {
        $uaiRepository = $doctrine->getManager()->getRepository(Uai::class);
        $uai = $user->getUai();
        // interdit l'accès à la page si:
        // le token associé au membre est null
        // le token enregistré en base et le token présent dans l'url ne sont pas égaux
        // le token date de plus de 24h

        if ($user->getToken() === null || $token !== $user->getToken() || !$this->isRequestInTime($user->getPasswordRequestedAt())) {
            $this->redirectToRoute('login');
        }
        $null_date = new DateTime(null);
        // réinitialisation du token à void pour qu'il ne soit plus réutilisable
        $user->setToken('void');
        $user->setPasswordRequestedAt($null_date);
        $user->setIsActive(1);
        $user->setUpdatedAt(new DateTime());
        $user->setLastVisit(new DateTime());
        $user->setRoles(['ROLE_PROF']);
        $em = $this->doctrine->getManager();
        $em->persist($user);
        $em->flush();
        $uai = $user->getUai();
        $uai_obj = $uaiRepository->findOneBy(['uai' => $uai]);
        $mailer->sendMessage($user, $uai_obj);
        $request->getSession()->getFlashBag()->add('success', "Votre inscription est terminée, vous pouvez vous connecter.");

        return $this->redirectToRoute('login');


    }

    // si supérieur à 24h, retourne false
    // sinon retourne false

    private function isRequestInTime(DateTime $passwordRequestedAt = null): bool
    {
        if ($passwordRequestedAt === null) {
            return false;
        }

        $now = new DateTime();
        $interval = $now->getTimestamp() - $passwordRequestedAt->getTimestamp();

        $daySeconds = 60 * 60 * 24;
        return !($interval > $daySeconds);
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route("/forgottenPassword", name: "forgotten_password")]
    public function forgottenPassword(Request $request, MailerInterface $mailer, ManagerRegistry $doctrine, TokenGeneratorInterface $tokenGenerator): RedirectResponse|Response
    {
        $session = $this->requestStack->getSession();
        $form = $this->createFormBuilder()
            ->add('email', EmailType::class, [
                'constraints' => [
                    new Email(),
                    new NotBlank()
                ]
            ])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $doctrine->getManager();

            $user = $em->getRepository(User::class)->findOneBy(['email' => ($form->getData()['email'])]);

            // aucun email associé à ce compte.
            if (!$user) {
                $this->requestStack->getSession()->set('info', 'Cet email ne correspond pas à un compte.');

                return $this->redirectToRoute('forgotten_password');
            }

            // création du token
            $user->setToken($tokenGenerator->generateToken());
            // enregistrement de la date de création du token
            $user->setPasswordRequestedAt(new DateTime());
            $em->persist($user);
            $em->flush();

            $email = (new TemplatedEmail())
                ->from(new Address('info@olymphys.fr', 'Équipe Olymphys'))
                ->to(new Address($user->getEmail(), $user->getNom()))
                ->subject('Renouvellement du mot de passe')
                ->htmlTemplate('email/password_mail.html.twig')
                ->context([
                    'user' => $user,
                ]);
            $mailer->send($email);
            $this->requestStack->getSession()->set('info', "Un mail va vous être envoyé afin que vous puissiez renouveler votre mot de passe. Le lien que vous recevrez sera valide 24h.");

            return $this->redirectToRoute("core_home");
        }

        return $this->render('security/password_request.html.twig', [
            'passwordRequestForm' => $form->createView()
        ]);
    }

    #[Route("/reset_password/{id}/{token}", name: "reset_password")]
    public function resetPassword(User $user, Request $request, string $token, UserPasswordHasherInterface $passwordEncoder): RedirectResponse|Response
    {

        // interdit l'accès à la page si:
        // le token associé au membre est null
        // le token enregistré en base et le token présent dans l'url ne sont pas égaux
        // le token date de plus de 10 minutes
        if ($user->getToken() === null || $token !== $user->getToken() || !$this->isRequestInTime($user->getPasswordRequestedAt())) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(ResettingType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $session = $this->requestStack->getSession();
            $user = $form->getData();

            $user->setPassword($passwordEncoder->hashPassword(
                $user,
                $form->get('plainPassword')->getData()));
            //$plainPassword = $form->getData();

            // réinitialisation du token à null pour qu'il ne soit plus réutilisable
            $user->setToken(null);
            $user->setPasswordRequestedAt(null);
            $user->setUpdatedAt(new DateTime('now'));
            $user->setLastVisit(new DateTime('now'));
            $em = $this->doctrine->getManager();
            $em->persist($user);
            $em->flush();
            $this->requestStack->getSession()->set('info', "Votre mot de passe a été renouvelé.");

            return $this->redirectToRoute('login');

        }

        return $this->render('security/reset_password.html.twig', [
            'resetPasswordForm' => $form->createView()
        ]);
    }

    protected function renderLogin(array $data): Response
    {
        return $this->render('security/login.html.twig', $data);
    }


}