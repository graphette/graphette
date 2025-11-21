<?php

namespace Graphette\Graphette\TypeRegistry;

class FieldInfo {

	/**
	 * @var ?array{
	 *     className: string,
	 *     method: string,
	 *     resolveInfoArgName: string|null,
	 *     objectValueArgName: string
	 *  }
	 */
	private ?array $resolveMethod = null;

	/**
	 * @var array<object>|null
	 */
	private ?array $fieldAttributes = [];

	public function getResolveMethod(): ?array {
		return $this->resolveMethod;
	}

	public function setResolveMethod(?array $resolveMethod): void {
		$this->resolveMethod = $resolveMethod;
	}

	public function getFieldAttributes(): ?array {
		return $this->fieldAttributes;
	}

	public function getFieldAttribute(string $type): ?object {
		foreach ($this->fieldAttributes as $fieldAttribute) {
			if ($fieldAttribute::class === $type) {
				return $fieldAttribute;
			}
		}

		return null;
	}

    public function hasFieldAttribute(string $type): bool {
        return $this->getFieldAttribute($type) !== null;
    }

    public function getFieldAttributesByType(string $type): array {
		$fieldAttributes = [];

		foreach ($this->fieldAttributes as $fieldAttribute) {
			if ($fieldAttribute instanceof $type) {
				$fieldAttributes[] = $fieldAttribute;
			}
		}

		return $fieldAttributes;
	}

	public function setFieldAttributes(?array $fieldAttributes): void {
		$this->fieldAttributes = $fieldAttributes;
	}

}
