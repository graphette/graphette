<?php declare(strict_types=1);
/**
 * @author Lukáš Jelič
 */

namespace Graphette\Graphette\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class ListOf {

    private string $type;

    public function __construct(string $type) {
        $this->type = $type;
    }

    public function getType(): string {
        return $this->type;
    }

}
