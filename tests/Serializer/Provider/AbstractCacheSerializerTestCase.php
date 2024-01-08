<?php
declare(strict_types=1);

namespace Kununu\Projections\Tests\Serializer\Provider;

use Kununu\Projections\Serializer\CacheSerializerInterface;
use Kununu\Projections\Tests\Stubs\ProjectionItem\ProjectionItemIterableStub;
use PHPUnit\Framework\TestCase;

abstract class AbstractCacheSerializerTestCase extends TestCase
{
    protected const RESULT_FILE = '';

    private CacheSerializerInterface $serializer;
    private ProjectionItemIterableStub $item;
    private mixed $serializedResult;

    public function testSerialize(): void
    {
        $result = $this->serializer->serialize($this->item);

        $this->assertEquals($this->serializedResult, $result);
    }

    public function testDeserialize(): void
    {
        $result = $this->serializer->deserialize($this->serializedResult, ProjectionItemIterableStub::class);

        $this->assertEquals($result, $this->item);
    }

    abstract protected function getSerializer(): CacheSerializerInterface;

    protected function setUp(): void
    {
        $this->serializer = $this->getSerializer();
        $this->item = new ProjectionItemIterableStub('123456789', 'My Name');
        $this->item->storeData(['extra' => ['address' => 'Whatever', 'ssid' => 45734, 'active' => true]]);
        $this->serializedResult = file_get_contents($this->getResultFile());
    }

    private function getResultFile(): string
    {
        return __DIR__ . '/resources/' . static::RESULT_FILE;
    }
}
