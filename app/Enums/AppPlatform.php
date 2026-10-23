<?php

namespace App\Enums;

enum AppPlatform: string
{
    case Web = 'web';
    case Android = 'android';
    case Windows = 'windows';
    case Ios = 'ios';
    case MacOs = 'macos';
    case Linux = 'linux';

    /**
     * Human readable label for the platform.
     */
    public function label(): string
    {
        return match ($this) {
            self::Web => 'Web App',
            self::Android => 'Android App',
            self::Windows => 'Windows App',
            self::Ios => 'iOS App',
            self::MacOs => 'macOS App',
            self::Linux => 'Linux App',
        };
    }

    /**
     * Short label used inside badges and chips.
     */
    public function shortLabel(): string
    {
        return match ($this) {
            self::Web => 'Web',
            self::Android => 'Android',
            self::Windows => 'Windows',
            self::Ios => 'iOS',
            self::MacOs => 'macOS',
            self::Linux => 'Linux',
        };
    }

    /**
     * Icon key understood by the front end icon map.
     */
    public function icon(): string
    {
        return match ($this) {
            self::Web => 'globe',
            self::Android => 'smartphone',
            self::Windows => 'monitor',
            self::Ios => 'apple',
            self::MacOs => 'laptop',
            self::Linux => 'terminal',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return array_combine(self::values(), array_map(
            fn (self $platform): string => $platform->label(),
            self::cases(),
        ));
    }

    /**
     * Options ready to be consumed by a select input.
     *
     * @return array<int, array{value: string, label: string, short_label: string, icon: string}>
     */
    public static function selectOptions(): array
    {
        return array_map(fn (self $platform): array => [
            'value' => $platform->value,
            'label' => $platform->label(),
            'short_label' => $platform->shortLabel(),
            'icon' => $platform->icon(),
        ], self::cases());
    }
}
