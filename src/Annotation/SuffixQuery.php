<?php

declare(strict_types=1);

namespace Jeanile\SuffixQuery\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
#[Attribute(Attribute::TARGET_CLASS)]
class SuffixQuery extends AbstractAnnotation
{
    public string $fields;
}
