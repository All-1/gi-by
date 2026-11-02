<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Domain;

/** Client UX phase for spec §3.1–§3.2. Mode chooses the form: block never uses the bottom bar. */
final class NotificationPresentation
{
    public const POPUP = 'popup';
    public const BOTTOM = 'bottom';
    public const BLOCK = 'block';

    /**
     * Block mode: first new-test view is a popup, every later view is the blocking overlay.
     * Bottom mode: first new-test view is a popup, then the bottom bar (including after the limit).
     *
     * @param bool $initialPopup New test may open with a popup. Failed test does not.
     */
    public static function resolve(
        int $dismissCount,
        int $dismissLimit,
        string $mode,
        bool $initialPopup = true,
    ): string {
        if ($mode === NotificationSettings::MODE_BLOCK) {
            if ($initialPopup && $dismissCount === 0 && $dismissCount < $dismissLimit) {
                return self::POPUP;
            }

            return self::BLOCK;
        }
        if ($initialPopup && $dismissCount === 0) {
            return self::POPUP;
        }

        return self::BOTTOM;
    }
}
