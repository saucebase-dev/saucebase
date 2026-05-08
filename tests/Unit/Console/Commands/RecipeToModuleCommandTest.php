<?php

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\SauceBase\RecipeToModuleCommand;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TestableRecipeToModuleCommand extends RecipeToModuleCommand
{
    public string $moduleName = '';

    public string $moduleFolder = '';

    public string $composerVendor = '';

    public string $composerType = '';

    public function setModuleNames(string $input): void
    {
        parent::setModuleNames($input);
    }

    public function placeholders(string $name): array
    {
        return parent::placeholders($name);
    }
}

class RecipeToModuleCommandTest extends TestCase
{
    private TestableRecipeToModuleCommand $command;

    protected function setUp(): void
    {
        parent::setUp();
        $this->command = new TestableRecipeToModuleCommand;
    }

    // ── Name normalization ────────────────────────────────────────────────────

    #[DataProvider('moduleNameInputProvider')]
    public function test_set_module_names_normalizes_input(string $input, string $expectedName, string $expectedFolder): void
    {
        $this->command->setModuleNames($input);

        $this->assertSame($expectedName, $this->command->moduleName);
        $this->assertSame($expectedFolder, $this->command->moduleFolder);
    }

    public static function moduleNameInputProvider(): array
    {
        return [
            'kebab-case input' => ['invoice-test', 'InvoiceTest', 'invoice-test'],
            'StudlyCase input' => ['InvoiceTest', 'InvoiceTest', 'invoice-test'],
            'single word kebab' => ['auth', 'Auth', 'auth'],
            'single word studly' => ['Auth', 'Auth', 'auth'],
            'multi-word kebab' => ['super-admin-panel', 'SuperAdminPanel', 'super-admin-panel'],
            'multi-word studly' => ['SuperAdminPanel', 'SuperAdminPanel', 'super-admin-panel'],
        ];
    }

    // ── Placeholder map ───────────────────────────────────────────────────────

    public function test_placeholders_module_base_variants(): void
    {
        $map = $this->command->placeholders('InvoiceTest');

        $this->assertSame('InvoiceTest', $map['{Module}']);
        $this->assertSame('invoicetest', $map['{module}']);
        $this->assertSame('Invoice_Test', $map['{Module_}']);
        $this->assertSame('invoice_test', $map['{module_}']);
        $this->assertSame('Invoice-Test', $map['{Module-}']);
        $this->assertSame('invoice-test', $map['{module-}']);
    }

    public function test_placeholders_module_camel_and_studly(): void
    {
        $map = $this->command->placeholders('InvoiceTest');

        $this->assertSame('invoiceTest', $map['{moduleCamel}']);
        $this->assertSame('InvoiceTest', $map['{ModuleStudly}']);
        $this->assertSame('InvoiceTest', $map['{moduleStudly}']);
    }

    public function test_placeholders_module_human_readable(): void
    {
        $map = $this->command->placeholders('InvoiceTest');

        $this->assertSame('Invoice Test', $map['{Module }']);
        $this->assertSame('invoice test', $map['{module }']);
        $this->assertSame('Invoice Test', $map['{ModuleTitle}']);
        $this->assertSame('Invoice Test', $map['{module_title}']);
    }

    public function test_placeholders_module_plural(): void
    {
        $map = $this->command->placeholders('InvoiceTest');

        $this->assertSame('invoice tests', $map['{module_plural}']);
        $this->assertSame('invoice_tests', $map['{module_plural_snake}']);
        $this->assertSame('invoice-tests', $map['{module_plural_kebab}']);
    }

    public function test_placeholders_model_variants(): void
    {
        $map = $this->command->placeholders('InvoiceTest');

        $this->assertSame('InvoiceTest', $map['{Model}']);
        $this->assertSame('invoicetest', $map['{model}']);
        $this->assertSame('invoice-test', $map['{model-}']);
        $this->assertSame('invoice_test', $map['{model_}']);
    }

    public function test_placeholders_js_friendly_tokens(): void
    {
        $map = $this->command->placeholders('InvoiceTest');

        $this->assertSame('InvoiceTest', $map['___Module___']);
        $this->assertSame('invoicetest', $map['___module___']);
    }

    public function test_placeholders_namespace_tokens(): void
    {
        $map = $this->command->placeholders('InvoiceTest');

        $this->assertSame('Modules', $map['{MODULE_NAMESPACE}']);
        $this->assertSame('Modules', $map['___MODULE_NAMESPACE___']);
    }

    public function test_placeholders_composer_vendor(): void
    {
        $this->command->composerVendor = 'saucebase';
        $map = $this->command->placeholders('InvoiceTest');

        $this->assertSame('saucebase', $map['{COMPOSER_VENDOR}']);
    }

    public function test_placeholders_composer_type(): void
    {
        $this->command->composerType = 'library';
        $map = $this->command->placeholders('InvoiceTest');

        $this->assertSame('library', $map['{COMPOSER_TYPE}']);
    }

    public function test_placeholders_single_word_module(): void
    {
        $map = $this->command->placeholders('Auth');

        $this->assertSame('Auth', $map['{Module}']);
        $this->assertSame('auth', $map['{module}']);
        $this->assertSame('auth', $map['{module-}']);
        $this->assertSame('auth', $map['{module_}']);
        $this->assertSame('Auth', $map['___Module___']);
    }
}
