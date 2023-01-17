<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Jeanile\SuffixQuery;

use Hyperf\HttpMessage\Server\Request\Parser;
use Hyperf\HttpMessage\Server\RequestParserInterface;
use Jeanile\SuffixQuery\Listener\ReverseMappingListener;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
                'class_map' => [
                ],
            ],
            'listeners' => [
                ReverseMappingListener::class,
            ],
        ];
    }
}
