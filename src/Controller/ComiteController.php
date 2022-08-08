<?php
// src/Controller/ComiteController.php
namespace App\Controller;

use App\Entity\Edition;
use App\Utils\ExcelCreate;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;

class ComiteController extends AbstractController
{

    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @IsGranted ("ROLE_COMITE")
     * @Route("/comite/accueil", name="comite_accueil")
     */
    public function accueil(): Response
    {
        return $this->render('comite/accueil.html.twig');
    }


    /**
     * @IsGranted ("ROLE_COMITE")
     * @Route("/comite/frais_lignes", name="comite_frais_lignes")
     */
    public function frais_lignes(Request $request)
    {
        // $user=$this->getUser();

        $repositoryEdition = $this->doctrine
            ->getManager()
            ->getRepository(Edition::class);

        $edition = $repositoryEdition->findOneBy([], ['id' => 'desc']);

        $task = ['message' => '1'];
        $form = $this->createFormBuilder($task)
            ->add('nblignes', IntegerType::class, ['label' => 'De combien de lignes avez vous besoin'])
            ->add('Entree', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $nblig = $data['nblignes'];

            return $this->redirectToroute('comite_frais', ['nblig' => $nblig]);
        }
        $content = $this->render('comite/frais_lignes.html.twig', ['edition' => $edition, 'form' => $form->createView()]);
        return new Response($content);
    }

    /**
     * @IsGranted ("ROLE_COMITE")
     *
     * @Route("/comite/frais,{nblig}", name="comite_frais", requirements={"nblig"="\d{1}|\d{2}"})
     */
    public function frais(Request $request, ExcelCreate $create, $nblig)
    {
        $repositoryEdition = $this->doctrine
            ->getManager()
            ->getRepository(Edition::class);

        $edition = $repositoryEdition->findOneBy([], ['id' => 'desc']);

        $task = ['nblig' => $nblig];

        $formBuilder = $this->createFormBuilder($task);

        for ($i = 1; $i <= $nblig; $i++) {
            $formBuilder->add('date' . $i, DateType::class, ['widget' => 'single_text'])
                ->add('designation' . $i, TextType::class)
                ->add('deplacement' . $i, MoneyType::class, ['required' => false])
                ->add('repas' . $i, MoneyType::class, ['required' => false])
                ->add('fournitures' . $i, MoneyType::class, ['required' => false])
                ->add('poste' . $i, MoneyType::class, ['required' => false])
                ->add('impressions' . $i, MoneyType::class, ['required' => false])
                ->add('autres' . $i, MoneyType::class, ['required' => false]);
        }
        $formBuilder->add('iban1', TextType::class, ['required' => false]);
        for ($j = 2; $j < 8; $j++) {
            $formBuilder->add('iban' . $j, NumberType::class, ['required' => false]);
        }
        $formBuilder->add('Entree', SubmitType::class);
        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            //dd($data);
            $nblig = $data['nblig'];

            $fichier = $create->excelfrais($edition, $data, $nblig);
            //dd($fichier);

            return $this->redirectToRoute('comite_envoi_frais', ['fichier' => $fichier]);

        }
        $content = $this->render('comite/frais.html.twig', ['edition' => $edition, 'nblig' => $nblig, 'form' => $form->createView()]);
        return new Response($content);

    }

    /**
     * @Route("/comite/envoi_frais {fichier}", name="comite_envoi_frais")
     * @throws TransportExceptionInterface
     */
    public function envoi_frais(Request $request, MailerInterface $mailer, $fichier)
    {
        $user = $this->getUser();
        $name = $user->getNom();
        $task = ['nblig' => 2];

        $formBuilder = $this->createFormBuilder($task);
        $formBuilder->add('choix', ChoiceType::class, ['choices' => ['Envoi par moi même' => true, 'Envoi Automatique' => false]])
            ->add('fichier', FileType::class);
        $formBuilder->add('Entree', SubmitType::class);
        $form = $formBuilder->getForm();
        //    dump($form);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = (new TemplatedEmail())
                ->from(new Address('info@olymphys.fr', 'Équipe Olymphys'))
                ->to(new Address($user->getEmail(), $user->getNom()))
                ->subject('Envoi de frais')
                ->htmlTemplate('email/envoi_frais.html.twig')
                ->context([
                    'user' => $user,
                ])
                ->attach($fichier);
            $mailer->send($email);

            return $this->redirectToroute('core_home');
        }
        $content = $this->render('comite/envoi_frais.html.twig', ['form' => $form->createView()]);
        return new Response($content);

    }


}