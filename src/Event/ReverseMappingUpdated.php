<?php

declare(strict_types=1);

namespace Jeanile\SuffixQuery\Event;

use Hyperf\Database\Model\Model;

class ReverseMappingUpdated
{
    public Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }
}
