<?php

namespace Swis\JsonApi\Client\Tests\Parsers;

use Swis\JsonApi\Client\Error;
use Swis\JsonApi\Client\ErrorCollection;
use Swis\JsonApi\Client\Exceptions\ValidationException;
use Swis\JsonApi\Client\Parsers\ErrorCollectionParser;
use Swis\JsonApi\Client\Parsers\ErrorParser;
use Swis\JsonApi\Client\Tests\AbstractTest;

class ErrorCollectionParserTest extends AbstractTest
{
    /**
     * @test
     */
    public function it_converts_data_to_error_collection()
    {
        $errorParser = $this->createMock(ErrorParser::class);
        $errorParser->expects($this->exactly(2))
            ->method('parse')
            ->willReturn(new Error());

        $parser = new ErrorCollectionParser($errorParser);
        $errorCollection = $parser->parse($this->getErrorCollection());

        $this->assertInstanceOf(ErrorCollection::class, $errorCollection);
        $this->assertEquals(2, $errorCollection->count());

        $this->assertInstanceOf(Error::class, $errorCollection->get(0));
        $this->assertInstanceOf(Error::class, $errorCollection->get(1));
    }

    /**
     * @test
     * @dataProvider provideInvalidData
     *
     * @param mixed $invalidData
     */
    public function it_throws_when_data_is_not_an_array($invalidData)
    {
        $parser = new ErrorCollectionParser($this->createMock(ErrorParser::class));

        $this->expectException(ValidationException::class);

        $parser->parse($invalidData);
    }

    public function provideInvalidData(): array
    {
        $object = new \stdClass();
        $object->foo = 'bar';

        return [
            [1],
            [1.5],
            [false],
            [null],
            ['foo'],
            [$object],
        ];
    }

    /**
     * @test
     */
    public function it_throws_when_data_is_empty()
    {
        $parser = new ErrorCollectionParser($this->createMock(ErrorParser::class));

        $this->expectException(ValidationException::class);

        $parser->parse([]);
    }

    /**
     * @return \stdClass
     */
    protected function getErrorCollection()
    {
        $data = [
            [
                'code' => 'json_client_content_id_in_object_not_equal_to_id_parameter',
            ],
            [
                'code' => 'json_client_content_id_in_object_not_equal_to_id_parameter',
            ],
        ];

        return json_decode(json_encode($data), false);
    }
}
