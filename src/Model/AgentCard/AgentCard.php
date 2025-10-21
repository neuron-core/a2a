<?php

declare(strict_types=1);

namespace NeuronCore\A2A\Model\AgentCard;

class AgentCard
{
    /**
     * @param array<AgentSkill> $skills
     * @param array<AgentInterface> $additionalInterfaces
     * @param array<string, mixed>|null $securitySchemes
     * @param array<array<string, mixed>>|null $security
     * @param array<AgentExtension> $extensions
     * @param array<AgentCardSignature> $signatures
     */
    public function __construct(
        public string $protocolVersion,
        public string $name,
        public string $description,
        public string $url,
        public string $preferredTransport,
        public string $version,
        public AgentProvider $provider,
        public array $skills = [],
        public array $additionalInterfaces = [],
        public bool $streaming = false,
        public bool $pushNotifications = false,
        public bool $stateTransitionHistory = false,
        public ?array $securitySchemes = null,
        public ?array $security = null,
        public array $extensions = [],
        public array $signatures = [],
    ) {
    }

    public function toArray(): array
    {
        $data = [
            'protocolVersion' => $this->protocolVersion,
            'name' => $this->name,
            'description' => $this->description,
            'url' => $this->url,
            'preferredTransport' => $this->preferredTransport,
            'version' => $this->version,
            'provider' => $this->provider->toArray(),
            'skills' => \array_map(fn (AgentSkill $skill): array => $skill->toArray(), $this->skills),
            'additionalInterfaces' => \array_map(fn (AgentInterface $interface): array => $interface->toArray(), $this->additionalInterfaces),
            'streaming' => $this->streaming,
            'pushNotifications' => $this->pushNotifications,
            'stateTransitionHistory' => $this->stateTransitionHistory,
        ];

        if ($this->securitySchemes !== null) {
            $data['securitySchemes'] = $this->securitySchemes;
        }

        if ($this->security !== null) {
            $data['security'] = $this->security;
        }

        if ($this->extensions !== []) {
            $data['extensions'] = \array_map(fn (AgentExtension $ext): array => $ext->toArray(), $this->extensions);
        }

        if ($this->signatures !== []) {
            $data['signatures'] = \array_map(fn (AgentCardSignature $sig): array => $sig->toArray(), $this->signatures);
        }

        return $data;
    }
}
