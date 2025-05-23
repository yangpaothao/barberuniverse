<?php

declare(strict_types=1);

namespace Square\Models\Builders;

use Core\Utils\CoreHelper;
use Square\Models\BulkUpsertLocationCustomAttributesRequest;
use Square\Models\BulkUpsertLocationCustomAttributesRequestLocationCustomAttributeUpsertRequest;

/**
 * Builder for model BulkUpsertLocationCustomAttributesRequest
 *
 * @see BulkUpsertLocationCustomAttributesRequest
 */
class BulkUpsertLocationCustomAttributesRequestBuilder
{
    /**
     * @var BulkUpsertLocationCustomAttributesRequest
     */
    private $instance;

    private function __construct(BulkUpsertLocationCustomAttributesRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Bulk Upsert Location Custom Attributes Request Builder object.
     *
     * @param array<string,BulkUpsertLocationCustomAttributesRequestLocationCustomAttributeUpsertRequest> $values
     */
    public static function init(array $values): self
    {
        return new self(new BulkUpsertLocationCustomAttributesRequest($values));
    }

    /**
     * Initializes a new Bulk Upsert Location Custom Attributes Request object.
     */
    public function build(): BulkUpsertLocationCustomAttributesRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
