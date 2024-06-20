<?php

namespace App\Tests\Functional;

use Override;
use Zenstruck\Browser\KernelBrowser;

class AppBrowser extends KernelBrowser
{
    /** Automatically sets mandatory Content-Type header on that request type */
    #[Override] public function patch(string $url, $options = []): self
    {
        $options["headers"] ??= [];
        $options["headers"]["Content-Type"] = "application/merge-patch+json";

        return $this->request('PATCH', $url, $options);
    }
}