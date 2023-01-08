<?php

declare(strict_types=1);

namespace Jeanile\SuffixQuery\Event;

use Hyperf\Database\Model\Model;

class ReverseMappingCreated
{
    public Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }
}
