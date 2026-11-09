<?php

namespace Wexample\SymfonyApiDs\Controller\Pages;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Wexample\SymfonyApi\Service\UserTokenService;
use Wexample\SymfonyApiDs\Service\FormProcessor\UserTokenIssueFormProcessor;
use Wexample\SymfonyApiDs\Traits\SymfonyApiDsBundleClassTrait;
use Wexample\SymfonyForms\Service\FormProcessor\Notification;
use Wexample\SymfonyLoader\Controller\AbstractPagesController;

/**
 * The signed-in account's api tokens, a tab of its profile: listed with when
 * each was last used, one issued — its secret shown once, on the page the
 * issuing leads back to —, one revoked. Answers 404 where the application has
 * no user tokens, or the account cannot hold one.
 */
#[Route(path: '/account/api-tokens')]
#[IsGranted('IS_AUTHENTICATED')]
final class UserTokensController extends AbstractPagesController
{
    use SymfonyApiDsBundleClassTrait;

    public const string ROUTE = 'user_api_tokens_index';

    public const string ROUTE_REVOKE = 'user_api_tokens_revoke';

    /** The id a row's buttons sign their token with, as the profile's do. */
    private const string CSRF_REVOKE = 'submit';

    private const string TRANSLATION_DOMAIN = 'WexampleSymfonyApiDsBundle.pages.user_tokens.index::';

    #[Route(path: '', name: self::ROUTE, methods: [Request::METHOD_GET])]
    public function index(
        Request $request,
        UserTokenService $tokens,
        UserTokenIssueFormProcessor $issueFormProcessor,
    ): Response {
        $user = $this->getUser();

        if (! $user || ! $tokens->isConfigured() || ! $tokens->canHold($user)) {
            throw $this->createNotFoundException();
        }

        return $this->renderPage('index', [
            'tokens' => $tokens->findTokens($user),
            'secret' => $issueFormProcessor->takeIssuedSecret($request->getSession()),
            'issue_form' => $issueFormProcessor->createForm()->createView(),
        ]);
    }

    #[Route(path: '/{id}/revoke', name: self::ROUTE_REVOKE, methods: [Request::METHOD_POST])]
    public function revoke(
        string $id,
        Request $request,
        UserTokenService $tokens,
    ): RedirectResponse {
        $user = $this->getUser();
        $token = $tokens->findTokenByReference($id);

        // Someone else's token answers as a missing one: ids are not probed.
        if (! $user || ! $token || $token->getClient()->getUserIdentifier() !== $user->getUserIdentifier()) {
            throw $this->createNotFoundException();
        }

        if (! $this->isCsrfTokenValid(self::CSRF_REVOKE, $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $tokens->revoke($token);
        $this->addFlash(
            Notification::TYPE_SUCCESS,
            (new Notification(self::TRANSLATION_DOMAIN . 'revoke.done', Notification::TYPE_SUCCESS))->toArray()
        );

        return $this->redirectToRoute(self::ROUTE);
    }
}
