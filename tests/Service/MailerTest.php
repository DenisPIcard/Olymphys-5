<?php

namespace App\Tests\Service;

use App\Entity\User;
use App\Service\Mailer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Twig\Environment;

class MailerTest extends TestCase
{

    public function testSendWelcomeMessage()
    {
        $symfonyMailer = $this->createMock(MailerInterface::class);
        $symfonyMailer->expects($this->once())
            ->method('send');
        $twig = $this->createMock(Environment::class);
        $user = new User();
        $user->setNom('Victor');
        $user->setEmail('victor@symfonycasts.com');

        $mailer = new Mailer($symfonyMailer, $twig);
        $email = $mailer->sendWelcomeMessage($user);
        $this->assertSame('Welcome to the Space Bar!', $email->getSubject());
        $this->assertCount(1, $email->getTo());
        /** @var Address[] $namedAddresses */
        $Addresses = $email->getTo();
        $this->assertInstanceOf(Address::class, $Addresses[0]);
        $this->assertSame('Victor', $Addresses[0]->getName());
        $this->assertSame('victor@symfonycasts.com', $Addresses[0]->getAddress());
    }
}
