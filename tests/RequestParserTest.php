<?php

declare(strict_types=1);

namespace NeuronCore\A2A\Tests;

use NeuronCore\A2A\Model\File\FileWithBytes;
use NeuronCore\A2A\Model\File\FileWithUri;
use NeuronCore\A2A\Model\Part\DataPart;
use NeuronCore\A2A\Model\Part\FilePart;
use NeuronCore\A2A\Model\Part\TextPart;
use NeuronCore\A2A\Server\RequestParser;
use PHPUnit\Framework\TestCase;

class RequestParserTest extends TestCase
{
    public function test_parses_message_send_params_with_new_task(): void
    {
        $params = [
            'messages' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['kind' => 'text', 'text' => 'Hello'],
                    ],
                ],
            ],
        ];

        $result = RequestParser::parseMessageSendParams($params);

        $this->assertNull($result->taskId);
        $this->assertCount(1, $result->messages);
        $this->assertSame('user', $result->messages[0]->role);
    }

    public function test_parses_message_send_params_with_existing_task(): void
    {
        $params = [
            'taskId' => 'task-123',
            'messages' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['kind' => 'text', 'text' => 'Continue task'],
                    ],
                ],
            ],
        ];

        $result = RequestParser::parseMessageSendParams($params);

        $this->assertSame('task-123', $result->taskId);
    }

    public function test_parses_text_part(): void
    {
        $params = [
            'messages' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['kind' => 'text', 'text' => 'Test message'],
                    ],
                ],
            ],
        ];

        $result = RequestParser::parseMessageSendParams($params);

        $part = $result->messages[0]->parts[0];
        $this->assertInstanceOf(TextPart::class, $part);
        $this->assertSame('Test message', $part->text);
    }

    public function test_parses_data_part(): void
    {
        $params = [
            'messages' => [
                [
                    'role' => 'user',
                    'parts' => [
                        [
                            'kind' => 'data',
                            'data' => ['key' => 'value'],
                            'mimeType' => 'application/json',
                        ],
                    ],
                ],
            ],
        ];

        $result = RequestParser::parseMessageSendParams($params);

        $part = $result->messages[0]->parts[0];
        $this->assertInstanceOf(DataPart::class, $part);
        $this->assertSame(['key' => 'value'], $part->data);
        $this->assertSame('application/json', $part->mimeType);
    }

    public function test_parses_file_part_with_bytes(): void
    {
        $params = [
            'messages' => [
                [
                    'role' => 'user',
                    'parts' => [
                        [
                            'kind' => 'file',
                            'file' => [
                                'bytes' => 'base64content',
                                'fileName' => 'test.txt',
                                'mimeType' => 'text/plain',
                            ],
                            'mimeType' => 'text/plain',
                        ],
                    ],
                ],
            ],
        ];

        $result = RequestParser::parseMessageSendParams($params);

        $part = $result->messages[0]->parts[0];
        $this->assertInstanceOf(FilePart::class, $part);
        $this->assertInstanceOf(FileWithBytes::class, $part->file);
        $this->assertSame('base64content', $part->file->bytes);
    }

    public function test_parses_file_part_with_uri(): void
    {
        $params = [
            'messages' => [
                [
                    'role' => 'user',
                    'parts' => [
                        [
                            'kind' => 'file',
                            'file' => [
                                'uri' => 'https://example.com/file.pdf',
                                'fileName' => 'file.pdf',
                                'mimeType' => 'application/pdf',
                            ],
                            'mimeType' => 'application/pdf',
                        ],
                    ],
                ],
            ],
        ];

        $result = RequestParser::parseMessageSendParams($params);

        $part = $result->messages[0]->parts[0];
        $this->assertInstanceOf(FilePart::class, $part);
        $this->assertInstanceOf(FileWithUri::class, $part->file);
        $this->assertSame('https://example.com/file.pdf', $part->file->uri);
    }

    public function test_parses_list_tasks_params(): void
    {
        $params = [
            'contextId' => 'context-123',
            'limit' => 10,
            'offset' => 5,
        ];

        $result = RequestParser::parseListTasksParams($params);

        $this->assertSame('context-123', $result->contextId);
        $this->assertSame(10, $result->limit);
        $this->assertSame(5, $result->offset);
    }

    public function test_parses_list_tasks_params_with_defaults(): void
    {
        $params = [];

        $result = RequestParser::parseListTasksParams($params);

        $this->assertNull($result->contextId);
        $this->assertNull($result->limit);
        $this->assertNull($result->offset);
    }

    public function test_parses_multiple_messages(): void
    {
        $params = [
            'messages' => [
                [
                    'role' => 'user',
                    'parts' => [['kind' => 'text', 'text' => 'Message 1']],
                ],
                [
                    'role' => 'agent',
                    'parts' => [['kind' => 'text', 'text' => 'Response 1']],
                ],
            ],
        ];

        $result = RequestParser::parseMessageSendParams($params);

        $this->assertCount(2, $result->messages);
        $this->assertSame('user', $result->messages[0]->role);
        $this->assertSame('agent', $result->messages[1]->role);
    }

    public function test_throws_exception_for_unknown_part_kind(): void
    {
        $params = [
            'messages' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['kind' => 'unknown', 'content' => 'test'],
                    ],
                ],
            ],
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown part kind: unknown');

        RequestParser::parseMessageSendParams($params);
    }
}
