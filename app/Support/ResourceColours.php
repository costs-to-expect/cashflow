<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Gives every resource a colour identity, used for its dot, bars and legends
 * wherever it appears (dashboard share bar, expense split rows, ...).
 *
 * Full Tailwind class strings, not built up from colour names - Tailwind only
 * generates classes it can see written out in full, and this directory is a
 * @source in resources/css/app.css. "hex" is a lighter shade of the same
 * colour that stays legible on the dark brand-gradient panels.
 */
class ResourceColours
{
    private const PALETTE = [
        ['bar' => 'bg-indigo-500', 'hex' => '#a5b4fc'],
        ['bar' => 'bg-rose-500', 'hex' => '#fda4af'],
        ['bar' => 'bg-emerald-500', 'hex' => '#6ee7b7'],
        ['bar' => 'bg-amber-500', 'hex' => '#fcd34d'],
        ['bar' => 'bg-sky-500', 'hex' => '#7dd3fc'],
        ['bar' => 'bg-orange-500', 'hex' => '#fdba74'],
    ];

    /**
     * @param  list<array{id: int|string}>  $resources  in display order
     * @return array<int|string, array{bar: string, hex: string}> keyed by resource id
     */
    public static function forResources(array $resources): array
    {
        $colours = [];

        foreach (array_values($resources) as $index => $resource) {
            $colours[$resource['id']] = self::PALETTE[$index % count(self::PALETTE)];
        }

        return $colours;
    }
}
