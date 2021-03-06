<?php
/**
 *
 *  * This file is part of JSON:API implementation for PHP.
 *  *
 *  * (c) Alexey Karapetov <karapetov@gmail.com>
 *  *
 *  * For the full copyright and license information, please view the LICENSE
 *  * file that was distributed with this source code.
 *
 */

declare(strict_types=1);

namespace JsonApiPhp\JsonApi\Test\Document\Resource\Relationship;

use JsonApiPhp\JsonApi\Document\Resource\NullData;
use JsonApiPhp\JsonApi\Document\Resource\Relationship\Linkage;
use JsonApiPhp\JsonApi\Document\Resource\ResourceId;
use JsonApiPhp\JsonApi\Test\HasAssertEqualsAsJson;
use PHPUnit\Framework\TestCase;

/**
 * Resource Linkage
 *
 * Resource linkage in a compound document allows a client to link together
 * all of the included resource objects without having to GET any URLs via links.
 *
 * Resource linkage MUST be represented as one of the following:
 * - null for empty to-one relationships.
 * - an empty array ([]) for empty to-many relationships.
 * - a single resource identifier object for non-empty to-one relationships.
 * - an array of resource identifier objects for non-empty to-many relationships.
 *
 * @see http://jsonapi.org/format/#document-resource-object-linkage
 * @see LinkageTest::testCanCreateNullLinkage()
 * @see LinkageTest::testCanCreateEmptyArrayLinkage()
 * @see LinkageTest::testCanCreateFromSingleResourceId()
 * @see LinkageTest::testCanCreateFromArrayOfResourceIds()
 */
class LinkageTest extends TestCase
{
    use HasAssertEqualsAsJson;

    public function testCanCreateNullLinkage()
    {
        $this->assertEqualsAsJson(
            null,
            Linkage::nullLinkage()
        );
    }

    public function testCanCreateEmptyArrayLinkage()
    {
        $this->assertEqualsAsJson(
            [],
            Linkage::emptyArrayLinkage()
        );
    }

    public function testCanCreateFromSingleResourceId()
    {
        $this->assertEqualsAsJson(
            [
                'type' => 'books',
                'id' => 'abc',
            ],
            Linkage::fromSingleResourceId(new ResourceId('books', 'abc'))
        );
    }

    public function testCanCreateFromArrayOfResourceIds()
    {
        $this->assertEqualsAsJson(
            [
                [
                    'type' => 'books',
                    'id' => 'abc',
                ],
                [
                    'type' => 'squirrels',
                    'id' => '123',
                ],
            ],
            Linkage::fromManyResourceIds(new ResourceId('books', 'abc'), new ResourceId('squirrels', '123'))
        );
    }

    public function testNullLinkageIsLinkedToNothing()
    {
        $apple = new ResourceId('apples', '1');
        $this->assertFalse(Linkage::nullLinkage()->isLinkedTo($apple));
        $this->assertFalse(Linkage::nullLinkage()->isLinkedTo(new NullData));
    }

    public function testEmptyArrayLinkageIsLinkedToNothing()
    {
        $apple = new ResourceId('apples', '1');
        $this->assertFalse(Linkage::emptyArrayLinkage()->isLinkedTo($apple));
        $this->assertFalse(Linkage::emptyArrayLinkage()->isLinkedTo(new NullData));
    }

    public function testSingleLinkageIsLinkedOnlyToItself()
    {
        $apple = new ResourceId('apples', '1');
        $orange = new ResourceId('oranges', '1');

        $linkage = Linkage::fromSingleResourceId($apple);

        $this->assertTrue($linkage->isLinkedTo($apple));
        $this->assertFalse($linkage->isLinkedTo($orange));
    }

    public function testMultiLinkageIsLinkedOnlyToItsMembers()
    {
        $apple = new ResourceId('apples', '1');
        $orange = new ResourceId('oranges', '1');
        $banana = new ResourceId('bananas', '1');

        $linkage = Linkage::fromManyResourceIds($apple, $orange);

        $this->assertTrue($linkage->isLinkedTo($apple));
        $this->assertTrue($linkage->isLinkedTo($orange));
        $this->assertFalse($linkage->isLinkedTo($banana));
    }
}
