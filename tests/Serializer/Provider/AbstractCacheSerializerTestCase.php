<?php
declare(strict_types=1);

namespace Kununu\Projections\Tests\Serializer\Provider;

use Kununu\Projections\Serializer\CacheSerializerInterface;
use PHPUnit\Framework\TestCase;

abstract class AbstractCacheSerializerTestCase extends TestCase
{
    protected const RESULT_FILE = '';

    private CacheSerializerInterface $serializer;
    private MyItemStub $item;
    private mixed $serializedResult;

    public function testSerialize(): void
    {
        $result = $this->serializer->serialize($this->item);

        $this->assertEquals($this->serializedResult, $result);
    }

    public function testDeserialize(): void
    {
        $result = $this->serializer->deserialize($this->serializedResult, MyItemStub::class);

        $this->assertEquals($result, $this->item);
    }

    abstract protected function getSerializer(): CacheSerializerInterface;

    protected function setUp(): void
    {
        $this->serializer = $this->getSerializer();
        $this->item = new MyItemStub(123456789, 'My Name');
        $this->item->storeData(['extra' => ['address' => 'Whatever', 'ssid' => 45734, 'active' => true]]);
        $this->serializedResult = file_get_contents($this->getResultFile());
    }

    private function getResultFile(): string
    {
        return __DIR__ . '/resources/' . static::RESULT_FILE;
    }
}
