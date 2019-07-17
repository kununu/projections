<?php declare(strict_types=1);

namespace Kununu\Projections\Tests\Unit\Repository;

use Psr\Cache\CacheItemInterface;

final class CacheItemStub implements CacheItemInterface
{
    private $key;
    private $value;
    private $isHit = false;
    private $tags = [];

    public function getKey()
    {
        return $this->key;
    }

    public function get()
    {
        return $this->value;
    }

    public function isHit()
    {
        return $this->isHit;
    }

    public function set($value)
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

    public function expiresAt($expiration): CacheItemInterface
    {
        return $this;
    }

    public function expiresAfter($time): CacheItemInterface
    {
        return $this;
    }

    public function tag($tags): CacheItemInterface
    {
        $this->tags = $tags;

        return $this;
    }

    public function getTags() : array
    {
        return $this->tags;
    }
}
