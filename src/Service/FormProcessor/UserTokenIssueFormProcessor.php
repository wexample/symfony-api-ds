<?php

namespace Wexample\SymfonyApiDs\Service\FormProcessor;

use DateTimeImmutable;
use InvalidArgumentException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Wexample\SymfonyApi\Service\UserTokenService;
use Wexample\SymfonyApiDs\Controller\Pages\UserTokensController;
use Wexample\SymfonyApiDs\Form\UserTokenIssueForm;
use Wexample\SymfonyForms\Service\FormProcessor\AbstractFormProcessor;

/**
 * A token issued to the signed-in account. Its secret exists in clear only
 * now: kept in the session for the page the form leads back to, which takes
 * it out as it shows it — once.
 */
class UserTokenIssueFormProcessor extends AbstractFormProcessor
{
    private const string SESSION_SECRET = 'wexample_symfony_api_ds.issued_secret';

    public function __construct(
        FormFactoryInterface $formFactory,
        RequestStack $requestStack,
        UrlGeneratorInterface $urlGenerator,
        private readonly Security $security,
        private readonly UserTokenService $tokens,
    ) {
        parent::__construct($formFactory, $requestStack, $urlGenerator);
    }

    /**
     * The secret of the token issued last, gone from the session once read.
     */
    public function takeIssuedSecret(SessionInterface $session): ?string
    {
        return $session->remove(self::SESSION_SECRET);
    }

    public function formIsValid(FormInterface $form): bool
    {
        if (! parent::formIsValid($form)) {
            return false;
        }

        $expiration = $form->get(UserTokenIssueForm::FIELD_EXPIRATION)->getData();

        if ($expiration instanceof DateTimeImmutable && $expiration <= new DateTimeImmutable()) {
            $form->get(UserTokenIssueForm::FIELD_EXPIRATION)->addError(new FormError('@form::error.expiration_past'));

            return false;
        }

        return true;
    }

    public function onValid(FormInterface $form)
    {
        $user = $this->security->getUser();

        if (! $user || ! $this->tokens->canHold($user)) {
            throw new AccessDeniedHttpException();
        }

        try {
            $secret = $this->tokens->issue(
                $user,
                $form->get(UserTokenIssueForm::FIELD_EXPIRATION)->getData(),
                (string) $form->get(UserTokenIssueForm::FIELD_LABEL)->getData(),
            );
        } catch (InvalidArgumentException) {
            // An expiration beyond the longest the application allows.
            $form->get(UserTokenIssueForm::FIELD_EXPIRATION)->addError(new FormError('@form::error.expiration_too_far'));

            return;
        }

        $this->request->getSession()->set(self::SESSION_SECRET, $secret);
        $this->redirectToRoute(UserTokensController::ROUTE);
    }
}
