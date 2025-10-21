<?php

declare(strict_types=1);

namespace NeuronCore\A2A\Tests;

use NeuronCore\A2A\Enum\TaskState;
use PHPUnit\Framework\TestCase;

class TaskStateTest extends TestCase
{
    public function test_terminal_states_are_identified_correctly(): void
    {
        $this->assertTrue(TaskState::COMPLETED->isTerminal());
        $this->assertTrue(TaskState::CANCELED->isTerminal());
        $this->assertTrue(TaskState::REJECTED->isTerminal());
        $this->assertTrue(TaskState::FAILED->isTerminal());
    }

    public function test_non_terminal_states_are_identified_correctly(): void
    {
        $this->assertFalse(TaskState::QUEUED->isTerminal());
        $this->assertFalse(TaskState::RUNNING->isTerminal());
        $this->assertFalse(TaskState::INPUT_REQUIRED->isTerminal());
        $this->assertFalse(TaskState::AUTH_REQUIRED->isTerminal());
    }

    public function test_task_state_has_correct_string_values(): void
    {
        $this->assertSame('queued', TaskState::QUEUED->value);
        $this->assertSame('running', TaskState::RUNNING->value);
        $this->assertSame('input-required', TaskState::INPUT_REQUIRED->value);
        $this->assertSame('auth-required', TaskState::AUTH_REQUIRED->value);
        $this->assertSame('completed', TaskState::COMPLETED->value);
        $this->assertSame('canceled', TaskState::CANCELED->value);
        $this->assertSame('rejected', TaskState::REJECTED->value);
        $this->assertSame('failed', TaskState::FAILED->value);
    }
}
