<?php

declare(strict_types=1);

namespace Square\Models\Builders;

use Core\Utils\CoreHelper;
use Square\Models\CustomAttributeDefinition;
use Square\Models\Error;
use Square\Models\RetrieveCustomerCustomAttributeDefinitionResponse;

/**
 * Builder for model RetrieveCustomerCustomAttributeDefinitionResponse
 *
 * @see RetrieveCustomerCustomAttributeDefinitionResponse
 */
class RetrieveCustomerCustomAttributeDefinitionResponseBuilder
{
    /**
     * @var RetrieveCustomerCustomAttributeDefinitionResponse
     */
    private $instance;

    private function __construct(RetrieveCustomerCustomAttributeDefinitionResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Retrieve Customer Custom Attribute Definition Response Builder object.
     */
    public static function init(): self
    {
        return new self(new RetrieveCustomerCustomAttributeDefinitionResponse());
    }

    /**
     * Sets custom attribute definition field.
     *
     * @param CustomAttributeDefinition|null $value
     */
    public function customAttributeDefinition(?CustomAttributeDefinition $value): self
    {
        $this->instance->setCustomAttributeDefinition($value);
        return $this;
    }

    /**
     * Sets errors field.
     *
     * @param Error[]|null $value
     */
    public function errors(?array $value): self
    {
        $this->instance->setErrors($value);
        return $this;
    }

    /**
     * Initializes a new Retrieve Customer Custom Attribute Definition Response object.
     */
    public function build(): RetrieveCustomerCustomAttributeDefinitionResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
