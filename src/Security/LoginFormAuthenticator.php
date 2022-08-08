<?php

namespace App\Security;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{

    use TargetPathTrait;

    private UserRepository $userRepository;
    private RouterInterface $router;

    public function __construct(UserRepository $userRepository, RouterInterface $router)
    {
        $this->userRepository = $userRepository;
        $this->router = $router;
    }

    public function supports(Request $request): bool
    {
        return $request->isMethod('POST') && $this->getLoginUrl($request) === $request->getRequestUri();
    }

    public function authenticate(Request $request): Passport
    {
        $username = $request->request->get('_username');
        $password = $request->request->get('_password');

        return new Passport(
            new UserBadge($username, function ($userIdentifier) {
                $user = $this->userRepository->findOneBy(['username' => $userIdentifier]);

                if (!$user) {
                    throw new UserNotFoundException();
                }
                return $user;
            }),
            new PasswordCredentials($password),
            [

                new CsrfTokenBadge(
                    'authenticate',
                    $request->request->get('_csrf_token')
                ),
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        /*  if ($target = $this->getTargetPath($request->getSession(), $firewallName)) {
              return new RedirectResponse($target);
          }*/
        return new RedirectResponse($this->router->generate('core_home'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->router->generate('login');
    }

}
