<?php

namespace App\Services;

class FrontendConfig
{
    /** @var array<string, mixed>|null */
    private ?array $data = null;

    public function getFramework(): ?string
    {
        return $this->load()['framework'] ?? null;
    }

    public function isDev(): bool
    {
        return ($this->load()['dev'] ?? false) === true;
    }

    /** @return array<string, mixed> */
    private function load(): array
    {
        if ($this->data === null) {
            $this->data = @json_decode((string) @file_get_contents(base_path('frontend.json')), true) ?? [];
        }

        return $this->data;
    }
}
