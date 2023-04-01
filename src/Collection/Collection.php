<?php

namespace Awwar\PhpHttpEntityManager\Collection;

use ArrayAccess;
use Countable;
use IteratorAggregate;

interface Collection extends Countable, IteratorAggregate, ArrayAccess
{
}
