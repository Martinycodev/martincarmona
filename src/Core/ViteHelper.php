<?php

namespace App\Core;

/**
 * Helper para cargar assets de Vite correctamente en dev y producción.
 *
 * En dev:  apunta al Vite dev server (localhost:5173) con HMR.
 * En prod: lee el manifest.json generado por `npm run build`.
 */
class ViteHelper
{
    private static bool $isDev;
    private static ?array $manifest = null;
    private static string $devServerUrl = 'http://localhost:5173';
    private static string $manifestPath;

    public static function init(bool $isDev, string $rootPath): void
    {
        self::$isDev        = $isDev;
        self::$manifestPath = $rootPath . '/public/dist/.vite/manifest.json';
    }

    /**
     * Genera los tags <link> y <script> para los entry points.
     * Uso: <?= Vite::tags('resources/js/app.js') ?>
     */
    public static function tags(string ...$entries): string
    {
        if (self::$isDev) {
            return self::devTags(...$entries);
        }
        return self::prodTags(...$entries);
    }

    private static function devTags(string ...$entries): string
    {
        $base = self::$devServerUrl;
        $html = '<script type="module" src="' . $base . '/@vite/client"></script>' . PHP_EOL;
        foreach ($entries as $entry) {
            if (str_ends_with($entry, '.css')) {
                // En dev, Vite sirve CSS como módulo JS con HMR
                $html .= '<script type="module" src="' . $base . '/' . ltrim($entry, '/') . '"></script>' . PHP_EOL;
            } else {
                $html .= '<script type="module" src="' . $base . '/' . ltrim($entry, '/') . '"></script>' . PHP_EOL;
            }
        }
        return $html;
    }

    private static function prodTags(string ...$entries): string
    {
        $manifest = self::loadManifest();
        $html     = '';

        foreach ($entries as $entry) {
            $key = ltrim($entry, '/');
            if (!isset($manifest[$key])) {
                continue;
            }

            $chunk = $manifest[$key];

            // CSS importado directamente
            foreach ($chunk['css'] ?? [] as $css) {
                $html .= '<link rel="stylesheet" href="/dist/' . $css . '">' . PHP_EOL;
            }

            // Preload de imports
            foreach ($chunk['imports'] ?? [] as $import) {
                if (isset($manifest[$import])) {
                    $html .= '<link rel="modulepreload" href="/dist/' . $manifest[$import]['file'] . '">' . PHP_EOL;
                }
            }

            $file = $chunk['file'];
            if (str_ends_with($file, '.css')) {
                $html .= '<link rel="stylesheet" href="/dist/' . $file . '">' . PHP_EOL;
            } else {
                $html .= '<script type="module" src="/dist/' . $file . '"></script>' . PHP_EOL;
            }
        }

        return $html;
    }

    private static function loadManifest(): array
    {
        if (self::$manifest === null) {
            if (!file_exists(self::$manifestPath)) {
                throw new \RuntimeException('Vite manifest not found. Run `npm run build` first.');
            }
            self::$manifest = json_decode(file_get_contents(self::$manifestPath), true);
        }
        return self::$manifest;
    }

    public static function asset(string $path): string
    {
        if (self::$isDev) {
            return self::$devServerUrl . '/' . ltrim($path, '/');
        }
        $manifest = self::loadManifest();
        $key      = ltrim($path, '/');
        return '/dist/' . ($manifest[$key]['file'] ?? $path);
    }
}
