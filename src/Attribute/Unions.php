<?php declare(strict_types=1);
/**
 * @author Lukáš Jelič
 */

namespace Graphette\Graphette\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Unions {

    private string $unionType;

    public function __construct(string $unionType) {
        $this->unionType = $unionType;
    }

    public function getUnionType(): string {
        return $this->unionType;
    }


}
