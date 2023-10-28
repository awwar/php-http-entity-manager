<?php

namespace Awwar\PhpHttpEntityManager\Metadata;

use Closure;

class CallbacksSettings
{
    private Closure $createRequestLayoutCallback;

    private Closure $updateRequestLayoutCallback;

    private Closure $relationMappingCallback;

    private Closure $listMappingCallback;

    public function __construct(
        ?string $relationMappingCallbackMethod = null,
        ?string $createRequestLayoutCallbackMethod = null,
        ?string $updateRequestLayoutCallbackMethod = null,
        ?string $listMappingCallbackMethod = null,
    ) {
        if ($methodName = $relationMappingCallbackMethod) {
            $this->relationMappingCallback = (function (...$payload) use ($methodName) {
                return $this->{$methodName}(...$payload);
            });
        } else {
            $this->relationMappingCallback = function () {
                return [];
            };
        }

        if ($methodName = $createRequestLayoutCallbackMethod) {
            $this->createRequestLayoutCallback = (function (...$payload) use ($methodName) {
                return $this->{$methodName}(...$payload);
            });
        } else {
            $this->createRequestLayoutCallback = function () {
                return [];
            };
        }

        if ($methodName = $updateRequestLayoutCallbackMethod) {
            $this->updateRequestLayoutCallback = (function (...$payload) use ($methodName) {
                return $this->{$methodName}(...$payload);
            });
        } else {
            $this->updateRequestLayoutCallback = function () {
                return [];
            };
        }

        if ($methodName = $listMappingCallbackMethod) {
            $this->listMappingCallback = (function (...$payload) use ($methodName) {
                return $this->{$methodName}(...$payload);
            });
        } else {
            $this->listMappingCallback = function (array $payload) {
                foreach ($payload as $elem) {
                    yield $elem;
                }
            };
        }
    }

    public function getCreateRequestLayoutCallback(): Closure
    {
        return $this->createRequestLayoutCallback;
    }

    public function getListMappingCallback(): Closure
    {
        return $this->listMappingCallback;
    }

    public function getRelationMappingCallback(): Closure
    {
        return $this->relationMappingCallback;
    }

    public function getUpdateRequestLayoutCallback(): Closure
    {
        return $this->updateRequestLayoutCallback;
    }
}
