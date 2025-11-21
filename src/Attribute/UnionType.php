<?php declare(strict_types=1);
/**
 * @author Lukáš Jelič
 */

namespace Graphette\Graphette\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class UnionType extends Type implements ResolvesType {

    private array $types;

    public function __construct(
        string $description = null,
        array  $types = [],
    ) {
        parent::__construct($description);
        $this->types = $types;
    }

    public function getTypes(): array {
        return $this->types;
    }



}
