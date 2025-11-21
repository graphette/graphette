<?php declare(strict_types=1);
/**
 * @author Lukáš Jelič
 */

namespace Graphette\Graphette\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class ObjectType extends Type implements ContainsFields {

    private ?string $resolver;

    public function __construct(
        string $description = null,
        string $resolver = null,
    ) {
        parent::__construct($description);
        $this->resolver = $resolver;
    }

    public function getResolver(): ?string {
        return $this->resolver;
    }


}
