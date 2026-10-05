<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Process\Process;
use Tests\TestCase;

/**
 * Modules patch this application's files when installed, and a patch anchors on the
 * lines around its change, comments included. Editing those lines here quietly breaks
 * installation; this fails first instead.
 *
 * Checked against the committed files (the index), not the working tree: a working
 * install has the patches applied and reformatted. Only in the contributor checkout,
 * since an installed application owns these files and may change them freely.
 */
class ModulePatchesTest extends TestCase
{
    /**
     * @return array<string, array{0: string}>
     */
    public static function patches(): array
    {
        return collect(glob(dirname(__DIR__, 2).'/modules/*/patches/*.patch') ?: [])
            ->mapWithKeys(function (string $path): array {
                $relative = substr($path, strlen(dirname(__DIR__, 2)) + 1);

                return [$relative => [$relative]];
            })
            ->all();
    }

    #[DataProvider('patches')]
    public function test_a_module_patch_applies_to_the_committed_files(string $patch): void
    {
        if (! is_dir(resource_path('js/vue')) || ! is_dir(resource_path('js/react'))) {
            $this->markTestSkipped('Installed application: it owns the patched files now.');
        }

        $applies = fn (string ...$flags): bool => (new Process(['git', 'apply', '--check', '--cached', ...$flags, $patch], base_path()))->run() === 0;

        $this->assertTrue($applies() || $applies('--reverse'), "{$patch} no longer applies to the committed files: regenerate it.");
    }
}
