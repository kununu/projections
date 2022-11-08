<?php
declare(strict_types=1);

namespace Kununu\Projections\Tests\Functional\Repository;

use Kununu\Projections\Repository\CachePoolProjectionRepository;
use Kununu\Projections\Tag\Tag;
use Kununu\Projections\Tag\Tags;
use Kununu\Projections\Tests\Functional\App\Repository\ProjectionItemStub;
use Kununu\TestingBundle\Test\FixturesAwareTestCase;
use Kununu\TestingBundle\Test\Options\Options;

final class CachePoolProjectionRepositoryTest extends FixturesAwareTestCase
{
    /** @var CachePoolProjectionRepository */
    private $projectionRepository;

    /** @var ProjectionItemStub */
    private $projectionItemStub;

    public function testGetItem(): void
    {
        $this->assertEquals(
            $this->projectionItemStub,
            $this->projectionRepository->get(new ProjectionItemStub('an_identifier'))
        );
    }

    public function testAdd(): void
    {
        $id = 'an_identifier_1';
        $item = new ProjectionItemStub($id);

        $this->assertNull($this->projectionRepository->get($item));

        $this->projectionRepository->add($item);

        $this->assertEquals(
            new ProjectionItemStub($id),
            $this->projectionRepository->get($item)
        );
    }

    public function testAddDeferred(): void
    {
        $item1Id = 'an_identifier_1';
        $item2Id = 'an_identifier_2';

        $item1 = new ProjectionItemStub($item1Id);
        $item2 = new ProjectionItemStub($item2Id);

        $this->assertNull($this->projectionRepository->get($item1));
        $this->assertNull($this->projectionRepository->get($item2));

        $this->projectionRepository->addDeferred($item1);
        $this->projectionRepository->addDeferred($item2);

        $this->projectionRepository->flush();

        $this->assertEquals(
            new ProjectionItemStub($item1Id),
            $this->projectionRepository->get($item1)
        );

        $this->assertEquals(
            new ProjectionItemStub($item2Id),
            $this->projectionRepository->get($item2)
        );
    }

    public function testDelete(): void
    {
        $item = new ProjectionItemStub('an_identifier');

        $this->assertEquals(
            $item,
            $this->projectionRepository->get($this->projectionItemStub)
        );

        $this->projectionRepository->delete($item);

        $this->assertNull($this->projectionRepository->get($this->projectionItemStub));
    }

    public function testDeleteByTags(): void
    {
        $item = new ProjectionItemStub('an_identifier');

        $this->assertEquals(
            $item,
            $this->projectionRepository->get($this->projectionItemStub)
        );

        $this->projectionRepository->deleteByTags(new Tags(new Tag('stub')));

        $this->assertNull($this->projectionRepository->get($this->projectionItemStub));
    }

    protected function setUp(): void
    {
        $this->loadCachePoolFixtures('app.cache.projections', Options::create());

        $this->projectionRepository = $this->getFixturesContainer()->get(CachePoolProjectionRepository::class);

        $projectionItemStub = new ProjectionItemStub('an_identifier');
        $this->projectionRepository->add($projectionItemStub);

        $this->projectionItemStub = $projectionItemStub;
    }
}
