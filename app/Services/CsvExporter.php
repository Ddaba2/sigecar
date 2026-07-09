<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExporter
{
    /**
     * Génère une réponse CSV en streaming avec BOM UTF-8.
     *
     * @param  string    $filename  Nom du fichier téléchargé
     * @param  array     $headers   Ligne d'en-tête
     * @param  iterable  $rows      Lignes de données (chaque ligne est un array)
     * @param  string    $separator Séparateur de colonnes (défaut : ';')
     */
    public static function stream(
        string $filename,
        array $headers,
        iterable $rows,
        string $separator = ';'
    ): StreamedResponse {
        return response()->streamDownload(
            static function () use ($headers, $rows, $separator) {
                $out = fopen('php://output', 'w');
                // BOM UTF-8 pour compatibilité Excel
                fprintf($out, "\xEF\xBB\xBF");
                fputcsv($out, $headers, $separator);
                foreach ($rows as $row) {
                    fputcsv($out, $row, $separator);
                }
                fclose($out);
            },
            $filename,
            ['Content-Type' => 'text/csv; charset=UTF-8']
        );
    }
}
