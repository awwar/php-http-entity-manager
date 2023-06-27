<?php

namespace Awwar\PhpHttpEntityManager\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class DefaultValue implements CacheableAnnotation
{
    public function __construct(
        private int|string|bool|array|float|null $value = EmptyValue::class,
        private array $callback = []
    ) {
    }

    public function toArray(): array
    {
        $value = EmptyValue::class;

        if ($this->value !== EmptyValue::class) {
            $value = $this->value;
        }
        $value = call_user_func($this->callback);

        return [
            'value' => $value
        ];
    }

    public static function getDefault(): array
    {
        return [
            'target' => Attribute::TARGET_PROPERTY,
            'targetName' => null,
            'data' => [
                'value' => EmptyValue::class,
            ],
        ];
    }
}