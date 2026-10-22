<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Infrastructure\Security;

/**
 * D2 (interim): personal-account roles that may open dealer Tests UI and use WS test commands.
 */
final class DealerTestsCapability
{
    public const CAP_ACCESS = 'bp_access_knowledge_tests';

    /** @var list<string> WP role slugs mapped to PA “dealer-side” users (subscriber → dealer in bp_contracts). */
    public const PA_ROLE_SLUGS = [
        'subscriber',
        'dealer',
        'free_dealer',
        'designer_dealer',
        'distributor',
        'designer',
    ];

    public function registerCaps(): void
    {
        foreach (self::PA_ROLE_SLUGS as $roleSlug) {
            $role = get_role($roleSlug);
            if ($role !== null) {
                $role->add_cap(self::CAP_ACCESS);
            }
        }
    }

    public function canAccess(?int $userId = null): bool
    {
        $userId ??= get_current_user_id();
        if ($userId <= 0) {
            return false;
        }

        if (user_can($userId, self::CAP_ACCESS)) {
            return (bool) apply_filters('bp_knowledge_tests_can_access_dealer_tests', true, $userId);
        }

        return (bool) apply_filters('bp_knowledge_tests_can_access_dealer_tests', false, $userId);
    }

    public function normalizedRoleSlug(?int $userId = null): string
    {
        $userId ??= get_current_user_id();
        $user = get_userdata($userId);
        $role = $user !== false && !empty($user->roles) ? (string) $user->roles[0] : '';

        return $role === 'subscriber' ? 'dealer' : $role;
    }
}
