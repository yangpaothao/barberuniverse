<?php

declare(strict_types=1);

namespace Square\Models\Builders;

use Core\Utils\CoreHelper;
use Square\Models\TerminalRefundQuerySort;

/**
 * Builder for model TerminalRefundQuerySort
 *
 * @see TerminalRefundQuerySort
 */
class TerminalRefundQuerySortBuilder
{
    /**
     * @var TerminalRefundQuerySort
     */
    private $instance;

    private function __construct(TerminalRefundQuerySort $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Terminal Refund Query Sort Builder object.
     */
    public static function init(): self
    {
        return new self(new TerminalRefundQuerySort());
    }

    /**
     * Sets sort order field.
     *
     * @param string|null $value
     */
    public function sortOrder(?string $value): self
    {
        $this->instance->setSortOrder($value);
        return $this;
    }

    /**
     * Unsets sort order field.
     */
    public function unsetSortOrder(): self
    {
        $this->instance->unsetSortOrder();
        return $this;
    }

    /**
     * Initializes a new Terminal Refund Query Sort object.
     */
    public function build(): TerminalRefundQuerySort
    {
        return CoreHelper::clone($this->instance);
    }
}
