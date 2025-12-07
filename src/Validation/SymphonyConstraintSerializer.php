<?php

namespace Graphette\Graphette\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\AtLeastOneOf;
use Symfony\Component\Validator\Constraints\Compound;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\CssColor;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotEqualTo;
use Symfony\Component\Validator\Constraints\Regex;

class SymphonyConstraintSerializer {

    private const CONSTRAINT_MAP = [
        Length::class => 'length',
        Regex::class => 'regex',
        CssColor::class => 'cssColor',
        EqualTo::class => 'eq',
        NotEqualTo::class => 'neq',
        LessThan::class => 'lt',
        LessThanOrEqual::class => 'lte',
        GreaterThan::class => 'gt',
        GreaterThanOrEqual::class => 'gte',
        All::class => 'all',
        Count::class => 'count',
    ];

    public static function serialize(Constraint $constraint): array {
        if (!isset(self::CONSTRAINT_MAP[$constraint::class])) {
            throw new \Exception('Constraint ' . $constraint::class . ' is not supported');
        }

        $ruleName = self::CONSTRAINT_MAP[$constraint::class];

        return array_merge(
            ['rule' => $ruleName,],
            self::$ruleName($constraint),
        );
    }

    private static function length(Length $length): array {
        return [
            'min' => $length->min,
            'max' => $length->max,
        ];
    }

    private static function regex(Regex $regex): array {
		return [
            'pattern' => $regex->pattern,
        ];
    }

    private static function cssColor(CssColor $cssColor): array {
        return [];
    }

    private static function eq(EqualTo $eq): array {
        return [
            'value' => $eq->value,
        ];
    }

    private static function neq(NotEqualTo $neq): array {
        return [
            'value' => $neq->value,
        ];
    }

    private static function lt(LessThan $lt): array {
        return [
            'value' => $lt->value,
        ];
    }

    private static function lte(LessThanOrEqual $lte): array {
        return [
            'value' => $lte->value,
        ];
    }

    private static function gt(GreaterThan $gt): array {
        return [
            'value' => $gt->value,
        ];
    }

    private static function gte(GreaterThanOrEqual $gte): array {
        return [
            'value' => $gte->value,
        ];
    }

    private static function all(All $all): array {
        return [
            'rules' =>array_map(fn(Constraint $constraint) => self::serialize($constraint), $all->constraints)
        ];
    }

    private static function count(Count $count): array {
        return [
            'min' => $count->min,
            'max' => $count->max,
        ];
    }

}
