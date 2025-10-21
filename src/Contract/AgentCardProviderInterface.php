<?php

declare(strict_types=1);

namespace NeuronCore\A2A\Contract;

use NeuronCore\A2A\Model\AgentCard\AgentCard;

interface AgentCardProviderInterface
{
    public function getAgentCard(): AgentCard;
}
