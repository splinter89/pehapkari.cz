<?php

declare(strict_types=1);

namespace Pehapkari\Statie\Tests\Posts\Year2018\Cart\Infrastructure;

use Pehapkari\Statie\Posts\Year2018\Cart\Domain\CartRepositoryInterface;
use Pehapkari\Statie\Posts\Year2018\Cart\Infrastructure\MemoryCartRepository;

final class MemoryCartRepositoryTest extends AbstractCartRepositoryTest
{
    protected function createRepository(): CartRepositoryInterface
    {
        return new MemoryCartRepository();
    }
}
