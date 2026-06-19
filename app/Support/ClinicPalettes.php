<?php

namespace App\Support;

class ClinicPalettes
{
    public const DEFAULT_KEY = 'green-col';

    public static function all(): array
    {
        return [
            [
                'key' => 'green-col',
                'name' => 'Green Col',
                'description' => 'Verde natural con crema suave.',
                'colors' => [
                    'shell_bg' => '#f4efe4',
                    'shell_surface' => '#f8f4eb',
                    'panel_bg' => '#ffffff',
                    'primary' => '#24543f',
                    'primary_dark' => '#173729',
                    'accent' => '#87a97f',
                    'highlight' => '#f6f2e8',
                    'text' => '#173729',
                    'muted' => '#5d7365',
                    'border' => '#dde3d6',
                    'hero_from' => '#214b39',
                    'hero_via' => '#2c664f',
                    'hero_to' => '#6e8f73',
                ],
            ],
            [
                'key' => 'ocean-care',
                'name' => 'Ocean Care',
                'description' => 'Azules serenos y fondo claro.',
                'colors' => [
                    'shell_bg' => '#edf4f8',
                    'shell_surface' => '#f7fbfd',
                    'panel_bg' => '#ffffff',
                    'primary' => '#1f5f7a',
                    'primary_dark' => '#163c4d',
                    'accent' => '#67a9c4',
                    'highlight' => '#eef8fb',
                    'text' => '#163c4d',
                    'muted' => '#5a7987',
                    'border' => '#d7e4ea',
                    'hero_from' => '#19495f',
                    'hero_via' => '#2b6f88',
                    'hero_to' => '#74a9bd',
                ],
            ],
            [
                'key' => 'sunset-care',
                'name' => 'Sunset Care',
                'description' => 'Terracota suave con crema.',
                'colors' => [
                    'shell_bg' => '#f7eee8',
                    'shell_surface' => '#fcf7f2',
                    'panel_bg' => '#ffffff',
                    'primary' => '#9f5b43',
                    'primary_dark' => '#6a3828',
                    'accent' => '#d59772',
                    'highlight' => '#fff5ef',
                    'text' => '#5f3427',
                    'muted' => '#8a685c',
                    'border' => '#ecd9cf',
                    'hero_from' => '#6d3d2f',
                    'hero_via' => '#a46349',
                    'hero_to' => '#d69b73',
                ],
            ],
            [
                'key' => 'lavender-night',
                'name' => 'Lavender Night',
                'description' => 'Moras suaves con contraste elegante.',
                'colors' => [
                    'shell_bg' => '#f2eef8',
                    'shell_surface' => '#faf7fd',
                    'panel_bg' => '#ffffff',
                    'primary' => '#5c4b8a',
                    'primary_dark' => '#342754',
                    'accent' => '#9b89c8',
                    'highlight' => '#f4f0fb',
                    'text' => '#342754',
                    'muted' => '#726788',
                    'border' => '#e0d8ef',
                    'hero_from' => '#352752',
                    'hero_via' => '#5d4b8a',
                    'hero_to' => '#9b89c8',
                ],
            ],
            [
                'key' => 'rose-care',
                'name' => 'Rose Care',
                'description' => 'Rosados sobrios con fondo limpio.',
                'colors' => [
                    'shell_bg' => '#f9eef1',
                    'shell_surface' => '#fff8fa',
                    'panel_bg' => '#ffffff',
                    'primary' => '#9a4c64',
                    'primary_dark' => '#6a3345',
                    'accent' => '#d59aae',
                    'highlight' => '#fff3f6',
                    'text' => '#5b2c3b',
                    'muted' => '#8c6672',
                    'border' => '#ecd8df',
                    'hero_from' => '#6d3347',
                    'hero_via' => '#a55570',
                    'hero_to' => '#d8a2b5',
                ],
            ],
            [
                'key' => 'amber-care',
                'name' => 'Amber Care',
                'description' => 'Dorado suave con contraste calido.',
                'colors' => [
                    'shell_bg' => '#faf4e8',
                    'shell_surface' => '#fffaf2',
                    'panel_bg' => '#ffffff',
                    'primary' => '#936628',
                    'primary_dark' => '#63431a',
                    'accent' => '#d0a35e',
                    'highlight' => '#fff7e8',
                    'text' => '#5a3d16',
                    'muted' => '#8b7253',
                    'border' => '#ebddc2',
                    'hero_from' => '#65441a',
                    'hero_via' => '#9a6d2d',
                    'hero_to' => '#d2a761',
                ],
            ],
        ];
    }

    public static function keys(): array
    {
        return array_column(self::all(), 'key');
    }

    public static function resolve(?string $key): array
    {
        foreach (self::all() as $palette) {
            if ($palette['key'] === $key) {
                return $palette;
            }
        }

        return self::all()[0];
    }
}
