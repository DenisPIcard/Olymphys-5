<?php
// src/Controller/ComiteController.php
namespace App\Controller;

use App\Entity\Edition;
use App\Entity\User;
use App\Utils\ExcelCreate;
use Doctrine\Persistence\ManagerRegistry;
use PhpOffice\PhpSpreadsheet\Exception;
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
use Symfony\Component\HttpFoundation\RedirectResponse;
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
    public function frais_lignes(Request $request): RedirectResponse|Response
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
     * @throws Exception
     */
    public function frais(Request $request, ExcelCreate $create, $nblig): RedirectResponse|Response
    {
        $repositoryEdition = $this->doctrine
            ->getManager()
            ->getRepository(Edition::class);

        $edition = $repositoryEdition->findOneBy([], ['id' => 'desc']);
        $user = $this->getUser();
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
        $formBuilder->add('Verification', SubmitType::class);

        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $nblig = $data['nblig'];

            $create->excelfrais($user,$edition, $data, $nblig);

        }
        $content = $this->render('comite/frais.html.twig', ['edition' => $edition, 'nblig' => $nblig, 'form' => $form->createView()]);
        return new Response($content);

    }

    /**
     * @Route("/comite/envoi_frais", name="comite_envoi_frais")
     * @throws TransportExceptionInterface
     */
    public function envoi_frais(Request $request, MailerInterface $mailer): RedirectResponse|Response
    {
        $user = $this->getUser();

        $defaultData = ['message' => 'Charger votre fichier de frais '];
        $form = $this->createFormBuilder($defaultData)
            ->add('fichier', FileType::class)
            ->add('save', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $fichier = $data['fichier'];
        }



            $email = (new TemplatedEmail())
                ->to (New Address('info@olymphys.fr', 'Équipe Olymphys'))
                ->from(new Address($user->getEmail(), $user->getNom()))
                ->subject('Envoi de frais')
                ->htmlTemplate('email/envoi_frais.html.twig')
                ->context([
                    'user' => $user,
                ])
                ->attach($fichier);
            $mailer->send($email);

            return $this->redirectToroute('core_home');

        $content = $this->render('comite/envoi_frais.html.twig');
        return new Response($content);

    }


}