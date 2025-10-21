<?php

declare(strict_types=1);

namespace NeuronCore\A2A\Tests;

use NeuronCore\A2A\Model\File\FileWithBytes;
use NeuronCore\A2A\Model\Part\DataPart;
use NeuronCore\A2A\Model\Part\FilePart;
use NeuronCore\A2A\Model\Part\TextPart;
use PHPUnit\Framework\TestCase;

class PartTest extends TestCase
{
    public function test_creates_text_part_and_converts_to_array(): void
    {
        $part = new TextPart('Hello World');

        $array = $part->toArray();

        $this->assertSame('text', $array['kind']);
        $this->assertSame('Hello World', $array['text']);
    }

    public function test_creates_data_part_with_default_mime_type(): void
    {
        $data = ['key' => 'value', 'number' => 42];
        $part = new DataPart($data);

        $array = $part->toArray();

        $this->assertSame('data', $array['kind']);
        $this->assertSame($data, $array['data']);
        $this->assertSame('application/json', $array['mimeType']);
    }

    public function test_creates_data_part_with_custom_mime_type(): void
    {
        $part = new DataPart(['status' => 'ok'], 'application/xml');

        $array = $part->toArray();

        $this->assertSame('application/xml', $array['mimeType']);
    }

    public function test_creates_file_part_with_bytes(): void
    {
        $file = new FileWithBytes(
            bytes: 'base64encodedcontent',
            fileName: 'test.txt',
            mimeType: 'text/plain'
        );

        $part = new FilePart($file, 'text/plain');

        $array = $part->toArray();

        $this->assertSame('file', $array['kind']);
        $this->assertSame('text/plain', $array['mimeType']);
        $this->assertIsArray($array['file']);
        $this->assertSame('base64encodedcontent', $array['file']['bytes']);
        $this->assertSame('test.txt', $array['file']['fileName']);
    }

    public function test_data_part_supports_mixed_data_types(): void
    {
        $complexData = [
            'string' => 'value',
            'number' => 123,
            'boolean' => true,
            'array' => [1, 2, 3],
            'nested' => ['a' => 'b'],
        ];

        $part = new DataPart($complexData);

        $array = $part->toArray();

        $this->assertSame($complexData, $array['data']);
    }
}
