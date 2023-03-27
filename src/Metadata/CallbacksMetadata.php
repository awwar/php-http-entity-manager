<?php

namespace Awwar\PhpHttpEntityManager\Metadata;

use Closure;

class CallbacksMetadata
{
    private Closure $createLayout;

    private Closure $updateLayout;

    private Closure $relationMapper;

    private Closure $listDetermination;

    public function __construct(
        ?string $relationMapperMethod = null,
        ?string $createLayoutMethod = null,
        ?string $updateLayoutMethod = null,
        ?string $listDeterminationMethod = null,
    ) {
        if ($methodName = $relationMapperMethod) {
            $this->relationMapper = (function (...$payload) use ($methodName) {
                return $this->{$methodName}(...$payload);
            });
        } else {
            $this->relationMapper = function () {
                return [];
            };
        }

        if ($methodName = $createLayoutMethod) {
            $this->createLayout = (function (...$payload) use ($methodName) {
                return $this->{$methodName}(...$payload);
            });
        } else {
            $this->createLayout = function () {
                return [];
            };
        }

        if ($methodName = $updateLayoutMethod) {
            $this->updateLayout = (function (...$payload) use ($methodName) {
                return $this->{$methodName}(...$payload);
            });
        } else {
            $this->updateLayout = function () {
                return [];
            };
        }

        if ($methodName = $listDeterminationMethod) {
            $this->listDetermination = (function (...$payload) use ($methodName) {
                return $this->{$methodName}(...$payload);
            });
        } else {
            $this->listDetermination = function (array $payload) {
                foreach ($payload as $elem) {
                    yield $elem;
                }
            };
        }
    }

    public function getCreateLayout(): Closure
    {
        return $this->createLayout;
    }

    public function getListDetermination(): Closure
    {
        return $this->listDetermination;
    }

    public function getRelationMapper(): Closure
    {
        return $this->relationMapper;
    }

    public function getUpdateLayout(): Closure
    {
        return $this->updateLayout;
    }
}
