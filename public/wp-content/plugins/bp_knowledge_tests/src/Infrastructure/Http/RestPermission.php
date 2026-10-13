<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Infrastructure\Http;

/**
 * Wave A (Phase 3): interim capability until D2 (test-factory roles) is decided.
 */
final class RestPermission
{
    public static function canManageCatalog(): bool
    {
        return is_user_logged_in() && current_user_can('manage_options');
    }
}
