<?php
declare(strict_types=1);

namespace Kununu\Projections\Tests\Repository;

use DateInterval;
use DateTimeInterface;
use Psr\Cache\CacheItemInterface;

final class CacheItemStub implements CacheItemInterface
{
    private mixed $value = null;
    private bool $isHit = false;
    private array $tags = [];

    public function __construct(private string $key)
    {
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function get(): mixed
    {
        return $this->value;
    }

    public function isHit(): bool
    {
        return $this->isHit;
    }

    public function set(mixed $value): CacheItemStub
    {
        $this->value = $value;

        return $this;
    }

    public function setHit(): CacheItemInterface
    {
        $this->isHit = true;

        return $this;
    }

    public function setNotHit(): CacheItemInterface
    {
        $this->isHit = false;

        return $this;
    }

    public function expiresAt(?DateTimeInterface $expiration): CacheItemInterface
    {
        return $this;
    }

    public function expiresAfter(DateInterval|int|null $time): CacheItemInterface
    {
        return $this;
    }

    public function tag(array $tags): CacheItemInterface
    {
        $this->tags = $tags;

        return $this;
    }

    public function getTags(): array
    {
        return $this->tags;
    }
}
