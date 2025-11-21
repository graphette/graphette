<?php declare(strict_types=1);
/**
 * @author Lukáš Jelič
 */

namespace Graphette\Graphette\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ScalarAlias {

    private string $name;

    public function __construct(string $name) {
        $this->name = $name;
    }

	public function getName(): string {
		return $this->name;
	}

}
