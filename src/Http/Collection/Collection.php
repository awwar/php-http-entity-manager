<?php

namespace Awwar\PhpHttpEntityManager\Http\Collection;

use ArrayAccess;
use Countable;
use IteratorAggregate;

interface Collection extends Countable, IteratorAggregate, ArrayAccess
{
}
