<?php

namespace Swis\JsonApi\Client\Tests\Parsers;

use Swis\JsonApi\Client\Exceptions\ValidationException;
use Swis\JsonApi\Client\Link;
use Swis\JsonApi\Client\Links;
use Swis\JsonApi\Client\Meta;
use Swis\JsonApi\Client\Parsers\LinksParser;
use Swis\JsonApi\Client\Parsers\MetaParser;
use Swis\JsonApi\Client\Tests\AbstractTest;

class LinksParserTest extends AbstractTest
{
    /**
     * @test
     */
    public function it_converts_data_to_links()
    {
        $parser = new LinksParser(new MetaParser());
        $links = $parser->parse($this->getLinks(), LinksParser::SOURCE_DOCUMENT);

        $this->assertInstanceOf(Links::class, $links);
        $this->assertCount(4, $links->toArray());

        /** @var \Swis\JsonApi\Client\Link $link */
        $link = $links->self;
        $this->assertInstanceOf(Link::class, $link);
        $this->assertEquals('http://example.com/articles', $link->getHref());
        $this->assertInstanceOf(Meta::class, $link->getMeta());
        $this->assertEquals(new Meta(['copyright' => 'Copyright 2015 Example Corp.']), $link->getMeta());

        /** @var null $link */
        $link = $links->prev;
        $this->assertNull($link);

        /** @var \Swis\JsonApi\Client\Link $link */
        $link = $links->next;
        $this->assertInstanceOf(Link::class, $link);
        $this->assertEquals('http://example.com/articles?page[offset]=2', $link->getHref());
        $this->assertNull($link->getMeta());

        /** @var \Swis\JsonApi\Client\Link $link */
        $link = $links->last;
        $this->assertInstanceOf(Link::class, $link);
        $this->assertEquals('http://example.com/articles?page[offset]=10', $link->getHref());
        $this->assertNull($link->getMeta());
    }

    /**
     * @test
     * @dataProvider provideInvalidData
     *
     * @param mixed $invalidData
     */
    public function it_throws_when_link_is_not_a_string_object_or_null($invalidData)
    {
        $parser = new LinksParser($this->createMock(MetaParser::class));

        $this->expectException(ValidationException::class);

        $parser->parse($invalidData, LinksParser::SOURCE_DOCUMENT);
    }

    public function provideInvalidData(): array
    {
        return [
            [[1]],
            [[1.5]],
            [[false]],
            [[[]]],
        ];
    }

    /**
     * @test
     */
    public function it_throws_when_self_link_is_null()
    {
        $parser = new LinksParser($this->createMock(MetaParser::class));

        $this->expectException(ValidationException::class);

        $parser->parse(json_decode('{"self": null}', false), LinksParser::SOURCE_DOCUMENT);
    }

    /**
     * @test
     */
    public function it_throws_when_related_link_is_null()
    {
        $parser = new LinksParser($this->createMock(MetaParser::class));

        $this->expectException(ValidationException::class);

        $parser->parse(json_decode('{"related": null}', false), LinksParser::SOURCE_DOCUMENT);
    }

    /**
     * @test
     */
    public function it_throws_when_error_links_misses_about_link()
    {
        $parser = new LinksParser($this->createMock(MetaParser::class));

        $this->expectException(ValidationException::class);

        $parser->parse(json_decode('{}', false), LinksParser::SOURCE_ERROR);
    }

    /**
     * @test
     */
    public function it_throws_when_relationship_links_misses_self_and_related_links()
    {
        $parser = new LinksParser($this->createMock(MetaParser::class));

        $this->expectException(ValidationException::class);

        $parser->parse(json_decode('{}', false), LinksParser::SOURCE_RELATIONSHIP);
    }

    /**
     * @test
     */
    public function it_throws_when_link_does_not_have_href_property()
    {
        $parser = new LinksParser($this->createMock(MetaParser::class));

        $this->expectException(ValidationException::class);

        $parser->parse(json_decode('{"self": {}}', false), LinksParser::SOURCE_DOCUMENT);
    }

    /**
     * @return \stdClass
     */
    protected function getLinks()
    {
        $data = [
            'self' => [
                'href' => 'http://example.com/articles',
                'meta' => [
                    'copyright' => 'Copyright 2015 Example Corp.',
                ],
            ],
            'prev' => null,
            'next' => [
                'href' => 'http://example.com/articles?page[offset]=2',
            ],
            'last' => 'http://example.com/articles?page[offset]=10',
        ];

        return json_decode(json_encode($data), false);
    }
}
