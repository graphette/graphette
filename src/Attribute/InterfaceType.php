<?php declare(strict_types=1);
/**
 * @author Lukáš Jelič
 */

namespace Graphette\Graphette\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class InterfaceType extends Type implements ContainsFields, ResolvesType {

    private string $attachedTrait;

    public function __construct(string $attachedTrait, string $description = null) {
        parent::__construct($description);
        $this->attachedTrait = $attachedTrait;
    }

    public function getAttachedTrait(): string {
        return $this->attachedTrait;
    }

}
