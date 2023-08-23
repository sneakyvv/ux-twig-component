<?php

declare(strict_types=1);

namespace Symfony\UX\TwigComponent;

final class Hierarchy
{
    public function __construct(private object $component, public ?self $parent = null)
    {
    }

    public function add(object $component): self
    {
        return new self($component, $this);
    }

    public function __call(string $name, array $arguments): mixed
    {
        if (isset($this->component->$name)) {
            // try property
            return $this->component->$name;
        }

        if ($this->component instanceof \ArrayAccess && isset($this->component[$name])) {
            return $this->component[$name];
        }

        $method = $this->normalizeMethod($name);

        return $this->component->$method(...$arguments);
    }

    private function normalizeMethod(string $name): string
    {
        if (method_exists($this->component, $name)) {
            return $name;
        }

        foreach (['get', 'is', 'has'] as $prefix) {
            if (method_exists($this->component, $method = sprintf('%s%s', $prefix, ucfirst($name)))) {
                return $method;
            }
        }

        throw new \InvalidArgumentException(sprintf('Component "%s" does not have a "%s" method.', $this->component::class, $name));
    }
}
