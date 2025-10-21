<?php

declare(strict_types=1);

namespace NeuronCore\A2A\Model\Request;

class ListTasksParams
{
    public function __construct(
        public ?string $contextId = null,
        public ?int $limit = null,
        public ?int $offset = null,
    ) {
    }
}
