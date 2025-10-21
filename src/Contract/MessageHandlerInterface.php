<?php

declare(strict_types=1);

namespace NeuronCore\A2A\Contract;

use NeuronCore\A2A\Model\Message;
use NeuronCore\A2A\Model\Task;

interface MessageHandlerInterface
{
    /**
     * @param array<Message> $messages
     */
    public function handle(Task $task, array $messages): Task;
}
