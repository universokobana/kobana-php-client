<?php

declare(strict_types=1);

namespace Kobana\Exceptions;

/**
 * Exception thrown when a requested resource is not found.
 */
class ResourceNotFoundException extends KobanaException
{
    protected string $resourceType;
    protected int|string $resourceId;

    public function __construct(string $resourceType, int|string $resourceId)
    {
        $this->resourceType = $resourceType;
        $this->resourceId = $resourceId;

        parent::__construct("{$resourceType} with ID {$resourceId} not found.");
    }

    public function getResourceType(): string
    {
        return $this->resourceType;
    }

    public function getResourceId(): int|string
    {
        return $this->resourceId;
    }
}
