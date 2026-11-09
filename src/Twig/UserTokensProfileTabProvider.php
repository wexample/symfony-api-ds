<?php

namespace Wexample\SymfonyApiDs\Twig;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Translation\TranslatorInterface;
use Wexample\SymfonyApi\Service\UserTokenService;
use Wexample\SymfonyApiDs\Controller\Pages\UserTokensController;
use Wexample\SymfonyUserDs\Interface\ProfileTabProviderInterface;

/**
 * The api tokens as a tab of the profile, for an account that can hold some.
 */
class UserTokensProfileTabProvider implements ProfileTabProviderInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly UserTokenService $tokens,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function getProfileTabs(): array
    {
        $user = $this->security->getUser();

        if (! $user || ! $this->tokens->isConfigured() || ! $this->tokens->canHold($user)) {
            return [];
        }

        return [[
            'label' => $this->translator->trans('WexampleSymfonyApiDsBundle.pages.user_tokens.index::tab'),
            'route' => UserTokensController::ROUTE,
            'match' => ['user_api_tokens_'],
            'icon' => 'ph:bold/key',
        ]];
    }
}
