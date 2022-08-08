<?php

namespace App\Controller;

use App\Entity\Elevesinter;
use App\Entity\Newsletter;
use App\Entity\User;
use App\Form\NewsletterType;
use App\Message\SendNewsletterMessage;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;


class NewsletterController extends AbstractController
{
    private EntityManagerInterface $em;
    private RequestStack $requestStack;
    private ManagerRegistry $doctrine;

    public function __construct(EntityManagerInterface $em, RequestStack $requestStack, ManagerRegistry $doctrine)
    {
        $this->em = $em;
        $this->requestStack = $requestStack;
        $this->doctrine = $doctrine;
    }

    /**
     * @Route("/newsletter/write,{id}", name="newsletter_write")
     * @IsGranted ("ROLE_SUPER_ADMIN")
     */
    public function write(Request $request, $id)
    {
        if ($id == 0) {
            $newsletter = new Newsletter();
            $textini = '';
        } else {

            $newsletter = $this->em->getRepository(Newsletter::class)->find(['id' => $id]);
            if ($newsletter->getEnvoyee() == false) {
                $this->redirectToRoute('newsletter_liste');

            }
            $textini = $newsletter->getTexte();

        }
        $form = $this->createForm(NewsletterType::class, $newsletter, ['textini' => $textini]);

        $form->handleRequest($request);
        if ($form->isSubmitted() and $form->isValid()) {


            $this->em->persist($newsletter);
            $this->em->flush();

            return $this->redirectToRoute('newsletter_liste');
        }

        return $this->render('newsletter/write.html.twig', ['form' => $form->createView()]);

    }

    /**
     * @Route("/newsletter/init", name="newsletter_init")
     * @IsGranted ("ROLE_SUPER_ADMIN")
     */
    public function init(Request $request)

    {
        $users = $this->em->getRepository(User::class)->findAll();
        foreach ($users as $user) {
            $user->setNewsletter(true);
            $this->em->persist($user);
            $this->em->flush();
        }
        return $this->redirectToRoute('newsletter_liste');

    }

    /**
     * @Route("/newsletter/delete", name="newsletter_delete")
     * @IsGranted ("ROLE_SUPER_ADMIN")
     */
    public function delete(Request $request): RedirectResponse

    {

        $id = $request->query->get('myModalID');
        $newsletter = $this->doctrine->getRepository(Newsletter::class)->find(['id' => $id]);
        if ($newsletter) {
            $this->em->remove($newsletter);
            $this->em->flush();

        }

        return $this->redirectToRoute('newsletter_liste');


    }


    /**
     * @Route("/newsletter/liste", name="newsletter_liste")
     * @IsGranted ("ROLE_SUPER_ADMIN")
     */
    public function liste(Request $request)
    {
        $newsletters = [];

        $newsletters = $this->em->getRepository(Newsletter::class)->createQueryBuilder('n')
            ->select()
            ->orderBy('n.createdAt', 'DESC')
            ->getQuery()->getResult();

        return $this->render('newsletter/liste.html.twig', ['newsletters' => $newsletters]);

    }

    /**
     * @Route("/newsletter/send,{id}", name="newsletter_send")
     * @IsGranted ("ROLE_SUPER_ADMIN")
     */
    public function send(Request $request, int $id, MessageBusInterface $messageBus)
    {
        $session = $this->requestStack->getSession();
        $newsletter = $this->em->getRepository(Newsletter::class)->find(['id' => $id]);
        $qb1 = $this->em->getRepository(User::class)->createQueryBuilder('u');

        switch ($newsletter->getDestinataires()) {
            case 'Tous':
                $qb1->where('u.newsletter = 1')
                    ->addOrderBy('u.nom', 'ASC');
                $listeDestinataires = $qb1->getQuery()->getResult();
                break;
            case 'Professeurs':

                $qb1->where('u.newsletter = 1')
                    ->addOrderBy('u.nom', 'ASC')
                    ->andWhere('u.roles  =:role')
                    ->setParameter('role', 'a:2:{i:0;s:9:"ROLE_PROF";i:1;s:9:"ROLE_USER";}');

                $listeDestinataires = $qb1->getQuery()->getResult();

                break;
            case 'Eleves' :
                $qb2 = $this->em->getRepository(Elevesinter::class)->createQueryBuilder('e');
                $qb2->leftJoin('e.equipe', 'eq')
                    ->andWhere('eq.edition =:edition')
                    ->setParameter('edition', $session->get('edition'));
                $listeDestinataires = $qb2->getQuery()->getResult();

                break;
        }
        $repositoryUser = $this->em->getRepository(User::class);
        $qb = $repositoryUser->createQueryBuilder('p');
        foreach ($listeDestinataires as $destinataire) {
            //$messageBus->dispatch($newsletterSend->send($prof->getId(), $newsletter->getId()));
            $messageBus->dispatch(new SendNewsletterMessage($destinataire->getId(), $newsletter->getId()));
            // system('"dir"');

        }
        $newsletter->setSendAt(new DateTimeImmutable('now'));
        $this->em->persist($newsletter);
        $this->em->flush();
        return $this->redirectToRoute('newsletter_liste');


    }

    /**
     * @Route("/newsletter/duplicate,{id}", name="newsletter_duplicate")
     * @IsGranted ("ROLE_SUPER_ADMIN")
     */
    public function duplicate(Request $request, int $id)
    {
        $newsletter = $this->em->getRepository(Newsletter::class)->find(['id' => $id]);
        $newsletterCopy = new Newsletter();
        $newsletterCopy->setName($newsletter->getName() . '(2)');
        $newsletterCopy->setTexte($newsletter->getTexte());
        $newsletterCopy->setDestinataires($newsletter->getDestinataires());
        $this->em->persist($newsletterCopy);
        $this->em->flush();
        return $this->redirectToRoute('newsletter_liste');
    }


    /**
     * @throws Exception
     */
    public function messengerConsume(KernelInterface $kernel): Response
    {
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'symfony console messenger:consume async',

        ]);

        // You can use NullOutput() if you don't need the output
        $output = new NullOutput();
        $application->run($input, $output);

        // return the output, don't use if you used NullOutput()

        // return new Response(""), if you used NullOutput()
        return new Response();
    }

    /**
     *
     * @Route ("/newsletter/desinscription,{userid}", name="newsletter_desinscription")
     * @throws TransportExceptionInterface
     */
    public function desinscription(Request $request, User $userid, MailerInterface $mailer)
    {

        $token = hash('sha256', uniqid());

        $userid->setToken($token);
        $em = $this->doctrine->getManager();
        $em->persist($userid);
        $em->flush();
        $email = (new TemplatedEmail())
            ->from('info@olymphys.fr')
            ->to($userid->getEmail())
            ->subject('Désincription de la newsletter OdPF')
            ->htmlTemplate('newsletter/desinscription_newsletter.html.twig')
            ->context(['token' => $token, 'user' => $userid]);

        $mailer->send($email);
        $request->getSession()->getFlashBag()->add('alert', "un mail vient de vous être envoyé pour que vous confirmiez votre désinscription aux newletter des OdPF");

        return $this->redirectToRoute('core_home');
    }

    /**
     *
     * @Route ("/newsletter/confirmDesinscription/{token}/{userid}", name="newsletter_confirm_desinscription")
     */
    public function confirmDesinscription(Request $request, $token, User $userid)
    {

        if ($userid->getToken() == $token) {
            $userid->setNewsletter(false);
            $userid->setToken(null);
            $em = $this->doctrine->getManager();
            $em->persist($userid);
            $em->flush();
        }
        $request->getSession()->getFlashBag()->add('success', "Vous êtes désinscrit(e) de la newsletter des OdPF");
        return $this->redirectToRoute('core_home');
    }


}