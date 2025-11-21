<?php declare(strict_types=1);
/**
 * @author Lukáš Jelič
 */

namespace Graphette\Graphette\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
abstract class Type {

    // todo this is wired, should be on child classes
    private const ATTRIBUTE_TO_TYPE_MAP = [
        ObjectType::class => \GraphQL\Type\Definition\ObjectType::class,
        InputObjectType::class => \GraphQL\Type\Definition\InputObjectType::class,
        EnumType::class => \GraphQL\Type\Definition\EnumType::class,
        UnionType::class => \GraphQL\Type\Definition\UnionType::class,
        InterfaceType::class => \GraphQL\Type\Definition\InterfaceType::class,
    ];

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

    public function getQraphQLTypeClassName(): string {
        return self::ATTRIBUTE_TO_TYPE_MAP[static::class];
    }

}
