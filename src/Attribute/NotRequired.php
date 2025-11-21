<?php declare(strict_types=1);
/**
 * @author Lukáš Jelič
 */

namespace Graphette\Graphette\Attribute;

use Attribute;

/**
 * This attribute is only there to allow additional NotNull behaviour, even though the field itself can be nullable.
 *
 * The reason for this is due to be able to implement partial updates, while still maintain some validation for NotNull values.
 *
 * Hopefully the GQL specs will allow this in the future, see related Github issue https://github.com/graphql/graphql-spec/issues/476
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class NotRequired {



}
