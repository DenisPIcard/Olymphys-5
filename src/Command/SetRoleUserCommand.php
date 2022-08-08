<?php

namespace App\Command;

use Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Command\Command;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SetRoleUserCommand extends Command
{
    private EntityManagerInterface $em;

    private ValidatorInterface $validator;

    public function __construct(EntityManagerInterface $em, ValidatorInterface $validator)
    {
        $this->em = $em;
        $this->validator = $validator;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('set-role-user')
            ->setDescription('Set the user\'s role.')
            ->setDefinition(array(
                new InputArgument('username', InputArgument::REQUIRED, 'Username'),
                new InputArgument('role', InputArgument::REQUIRED, 'Nouveau role'),
            ));
    }

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $username = $input->getArgument('username');

        $user = $this->em->getRepository(User::class)->findOneBy(['username' => $username]);

        $role = $input->getArgument('role');

        $roles = $user->setRoles([$role]);

        $errors = $this->validator->validate($user);

        if (count($errors) > 0) {
            $errorsString = (string)$errors;
            throw new Exception($errorsString);
        }

        $this->em->flush();

        $output->writeln(sprintf('Nouveau rôle défini pour %s', $username));
    }

    /**
     * {@inheritdoc}
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $questions = array();

        if (!$input->getArgument('username')) {
            $question = new Question('L\'username de l\'utilisateur : ');
            $questions['username'] = $question;
        }

        if (!$input->getArgument('role')) {
            $question = new Question('Son nouveau rôle : ');
            $questions['role'] = $question;
        }

        foreach ($questions as $name => $question) {
            $answer = $this->getHelper('question')->ask($input, $output, $question);
            $input->setArgument($name, $answer);
        }
    }
}