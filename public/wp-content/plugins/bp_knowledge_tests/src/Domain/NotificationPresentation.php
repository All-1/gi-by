<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Domain;

/** Client UX phase for spec §3.1–§3.2 (popup → block or bottom). */
final class NotificationPresentation
{
    public const POPUP = 'popup';
    public const BOTTOM = 'bottom';
    public const BLOCK = 'block';

    public static function resolve(int $dismissCount, int $dismissLimit, string $mode): string
    {
        if ($dismissCount < $dismissLimit) {
            return self::POPUP;
        }

        return $mode === NotificationSettings::MODE_BLOCK ? self::BLOCK : self::BOTTOM;
    }
}
