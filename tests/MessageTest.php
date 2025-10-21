<?php

declare(strict_types=1);

namespace NeuronCore\A2A\Tests;

use NeuronCore\A2A\Model\Message;
use NeuronCore\A2A\Model\Part\TextPart;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    public function test_creates_message_with_single_part(): void
    {
        $message = new Message(
            role: 'user',
            parts: [new TextPart('Hello, agent!')]
        );

        $this->assertSame('user', $message->role);
        $this->assertCount(1, $message->parts);
        $this->assertInstanceOf(TextPart::class, $message->parts[0]);
    }

    public function test_creates_message_with_multiple_parts(): void
    {
        $message = new Message(
            role: 'agent',
            parts: [
                new TextPart('First part'),
                new TextPart('Second part'),
            ]
        );

        $this->assertCount(2, $message->parts);
    }

    public function test_converts_message_to_array(): void
    {
        $message = new Message(
            role: 'user',
            parts: [new TextPart('Test message')]
        );

        $array = $message->toArray();

        $this->assertSame('user', $array['role']);
        $this->assertIsArray($array['parts']);
        $this->assertCount(1, $array['parts']);
        $this->assertSame('text', $array['parts'][0]['kind']);
        $this->assertSame('Test message', $array['parts'][0]['text']);
    }

    public function test_supports_agent_role(): void
    {
        $message = new Message(
            role: 'agent',
            parts: [new TextPart('Response')]
        );

        $this->assertSame('agent', $message->role);
    }
}
