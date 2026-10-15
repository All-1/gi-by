<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Infrastructure\Http;

use BpKnowledgeTests\Infrastructure\Security\CatalogCapability;

final class RestPermission
{
    public static function canManageCatalog(): bool
    {
        return CatalogCapability::canManageCatalog();
    }
}
