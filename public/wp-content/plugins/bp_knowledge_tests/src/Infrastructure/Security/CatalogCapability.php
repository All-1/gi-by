<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Infrastructure\Security;

/**
 * D2 (interim): factory test-factory roles + WP administrators. Filter for product overrides.
 */
final class CatalogCapability
{
    public const CAP_MANAGE_CATALOG = 'bp_manage_knowledge_tests_catalog';

    /** @var list<string> */
    public const FACTORY_CATALOG_ROLES = [
        'administrator',
        'sales_manager',
    ];

    public static function registerCaps(): void
    {
        foreach (self::FACTORY_CATALOG_ROLES as $roleSlug) {
            $role = get_role($roleSlug);
            if ($role !== null) {
                $role->add_cap(self::CAP_MANAGE_CATALOG);
            }
        }

        foreach (wp_roles()->roles as $roleSlug => $roleData) {
            if (empty($roleData['capabilities']['manage_options'])) {
                continue;
            }
            $role = get_role($roleSlug);
            if ($role !== null) {
                $role->add_cap(self::CAP_MANAGE_CATALOG);
            }
        }
    }

    public static function canManageCatalog(?int $userId = null): bool
    {
        $userId ??= get_current_user_id();
        if ($userId <= 0) {
            return false;
        }

        if (user_can($userId, self::CAP_MANAGE_CATALOG) || user_can($userId, 'manage_options')) {
            return true;
        }

        return (bool) apply_filters('bp_knowledge_tests_can_manage_catalog', false, $userId);
    }

    public static function isFactoryCatalogRole(string $roleSlug): bool
    {
        return in_array($roleSlug, self::FACTORY_CATALOG_ROLES, true);
    }
}
