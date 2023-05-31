<?php

namespace App\Service;

use App\Entity\Equipesadmin;
use App\Entity\Uai;
use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Twig\Environment;

class Mailer
{
    private $requestStack;
    private MailerInterface $mailer;
    private $twig;

    public function __construct(MailerInterface $mailer, Environment $twig, RequestStack $requestStack)
    {

        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->requestStack = $requestStack;
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendMessage(User $user, Uai $uai_obj): TemplatedEmail
    {
        $email = (new TemplatedEmail())
            ->from(new Address('info@olymphys.fr'))
            ->to('info@olymphys.fr')
            ->subject('Inscription d\'un nouvel utilisateur')
            ->htmlTemplate('email/nouvel_utilisateur.html.twig')
            ->context([
                'user' => $user,
                'uai' => $uai_obj
            ]);
        $this->mailer->send($email);
        return $email;
    }


    /**
     * @throws TransportExceptionInterface
     */
    public function SendVerifEmail(User $user): TemplatedEmail
    {
        $email = (new TemplatedEmail())
            ->from('info@olymphys.fr')
            ->to($user->getEmail())//new Address($user->getEmail())
            ->subject('Olymphys-Confirmation de votre inscription')

            // path of the Twig template to render
            ->htmlTemplate('email/bienvenue.html.twig')

            // pass variables (name => value) to the template
            ->context([
                'expiration_date' => new \DateTime('+24 hours'),
                'user' => $user
            ]);
        $this->mailer->send($email);
        return $email;
    }


    /**
     * @throws TransportExceptionInterface
     */
    public function sendConfirmFile(Equipesadmin $equipe, $type_fichier, $user): Email
    {
        $email = (new TemplatedEmail())
            ->from('info@olymphys.fr')
            ->to('webmestre2@olymphys.fr')//'webmestre2@olymphys.fr', 'Denis'
            ->addCc('webmestre3@olymphys.fr')
            ->htmlTemplate('email/confirm_fichier.html.twig')
            ->subject('Depot du fichier ' . $type_fichier . ' de l\'équipe ' . $equipe->getInfoequipe())
            //->text('L\'equipe ' . $equipe->getInfoequipe() . ' a déposé un fichier : ' . $type_fichier)
            ->context(['equipe' => $equipe, 'typeFichier' => $type_fichier, 'userNom' => $user->getPrenomNom()]);

        $this->mailer->send($email);
        return $email;

    }


    /**
     * @throws TransportExceptionInterface
     */
    public function sendFrais($fichier, $user): TemplatedEmail
    {
        $email = (new TemplatedEmail())
            ->from('info@olymphys.fr')
            ->to(new Address($user->getEmail(), $user->getNom()))
            // ->from($user->getEmail())
            ->subject('Envoi de frais')
            ->htmlTemplate('email/envoi_des_frais.html.twig')
            ->attach(fopen($fichier, 'r'))
            ->context([
                'user' => $user,
            ]);

        $this->mailer->send($email);
        return $email;
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendConfirmeInscriptionEquipe(Equipesadmin $equipe, $user, $modif, $checkChange): Email
    {

        if (!$modif) {
            $email = (new TemplatedEmail())
                ->from('info@olymphys.fr')
                ->to('webmestre2@olymphys.fr') //'webmestre2@olymphys.fr', 'Denis'
                ->cc($user->getEmail())
                ->addCc('webmestre3@olymphys.fr')
                ->addCc('emma.gosse@orange.fr')
                ->htmlTemplate('email/confirme_inscription.html.twig')
                ->subject('Inscription de l\'équipe  ' . $equipe->getNumero() . ' par ' . $user->getPrenomNom())
                ->context(['equipe' => $equipe, 'userNom' => $user->getPrenomNom(), 'userMail' => $user->getEmail()]);//la valeur de la variable user ne passe pas dans le template, sécurité ? D'où les strings Nom et email
            /* ->attachFromPath('docequipes/30-fiche matériel-sécurité.doc');
         ->html('Bonjour<br>
                     Nous confirmons que ' . $equipe->getIdProf1()->getPrenomNom() . '(<a href="' . $user->getEmail() . '">' . $user->getEmail() .
             '</a>) du lycée ' . $equipe->getNomLycee() . ' de ' . $equipe->getLyceeLocalite() . ' a inscrit une nouvelle équipe denommée : ' . $equipe->getTitreProjet() .
             '<br>Veuillez prendre connaissance de la fiche sécurité disponible dans votre espace pour vérifier que les expériences de votre projet sont conformes aux exigences de sécurité imposées dans un établissement scolaire
              <br>Le comité national des Olympiades de Physique');
         */
        }
        if ($modif) {
            $changetext = '';
            if ($checkChange != null) {
                if (isset($checkChange['inscrite'])) {
                    if ($checkChange['inscrite'] == 'NON') {
                        $changetext = '<h1>Desinscription de l\'équipe !</h1><br>';
                        $checkChange['inscrite'] = $equipe->getIdProf1()->getPrenomNom() . '(<a href="' . $user->getEmail() . '">' . $user->getEmail() .
                            '</a>) du lycée ' . $equipe->getNomLycee() . ' de ' . $equipe->getLyceeLocalite() . ' a désinscrit l\'équipe denommée : ' . $equipe->getTitreProjet();
                    }
                    if ($checkChange['inscrite'] == 'OUI') {
                        $changetext = '<h1>Réinscription de l\'équipe !</h1><br>';
                        $checkChange['inscrite'] = $equipe->getIdProf1()->getPrenomNom() . '(<a href="' . $user->getEmail() . '">' . $user->getEmail() .
                            '</a>) du lycée ' . $equipe->getNomLycee() . ' de ' . $equipe->getLyceeLocalite() . ' a réinscrit l\'équipe denommée : ' . $equipe->getTitreProjet();

                    }

                }
                foreach ($checkChange as $change) {

                    $changetext .= ' - ' . $change . '<br>';

                }
            }

            $email = (new Email())
                ->from('info@olymphys.fr')
                ->to('webmestre2@olymphys.fr') //'webmestre2@olymphys.fr', 'Denis'
                ->cc('webmestre3@olymphys.fr')
                ->addCc('emma.gosse@orange.fr')
                ->subject('Modification de l\'équipe ' . $equipe->getTitreProjet() . ' par ' . $user->getPrenomNom())
                ->html('Bonjour<br>' .
                    $equipe->getIdProf1()->getPrenomNom() . '( <a href="' . $user->getEmail() . '">' . $user->getEmail() .
                    '</a>)  du lycée ' . $equipe->getNomLycee() . ' de ' . $equipe->getLyceeLocalite() . ' a modifié l\'équipe  n° ' . $equipe->getNumero() . ' : ' . $equipe->getTitreProjet()
                    . '<br> Modifications apportées :<br>' . $changetext . '<br> <br>Le comité national des Olympiades de Physique France');
        }
        $this->mailer->send($email);

        return $email;

    }


}