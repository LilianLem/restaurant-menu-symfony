<?php

namespace App\Tests\Functional;

use Override;
use Zenstruck\Foundry\Test\ResetDatabase;

class MenuSectionResourceTest extends ApiTestCase
{
    use ResetDatabase;
    use UserToSectionPopulateTrait;

    #[Override] protected function setUp(): void
    {
        $this->populate();
    }

    public function testPatchMenuSection(): void
    {
        // TODO: implement test
    }
}