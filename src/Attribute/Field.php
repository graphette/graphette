<?php declare(strict_types=1);
/**
 * @author Lukáš Jelič
 */

namespace Graphette\Graphette\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Field {

    private ?string $description;

    public function __construct(string $description = null) {
        $this->description = $description;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string {
        return $this->description;
    }

}
