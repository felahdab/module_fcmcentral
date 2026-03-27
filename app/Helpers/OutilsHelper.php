<?php

namespace Modules\FcmCentral\Helpers;

class OutilsHelper
{
    public static function selectColors(): array
    {
        return [
            'info' => 'Bleu',
            'success' => 'Vert',
            'gray' => 'Gris',
            'warning' => 'Orange',
            'danger' => 'Rouge',
            'brown' => 'Marron',
            'purple' => 'Violet',
        ];
    }

    public static function sanitizeForAlpine(?string $text): string
    {
        if (empty($text)) {
            return '';
        }

        // Remplace les caractères problématiques
        $text = str_replace(
            // ["'", '"', "\n", "\r", "\t", "\\", "`"],
            // ["&apos;", "&quot;", " ", "", " ", "", ""],
            ['"', "\n", "\r", "\t", '\\', '`'],
            ['&quot;', ' ', '', ' ', '', ''],
            $text
        );

        // Supprime les caractères de contrôle
        $text = preg_replace('/[\x00-\x1F\x7F]/u', '', $text);

        // Limite la longueur si nécessaire
        return trim(substr($text, 0, 1000));
    }
}
