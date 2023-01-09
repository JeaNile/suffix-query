<?php

declare(strict_types=1);

namespace Jeanile\SuffixQuery\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class SuffixQuery extends AbstractAnnotation
{
    public string $fields;

    public int $limit = 20;
}
