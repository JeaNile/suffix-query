<?php

declare(strict_types=1);

namespace Jeanile\SuffixQuery\Aspect;

use _PHPStan_acbb55bae\Psr\Http\Message\RequestInterface;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\AroundInterface;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Jeanile\SuffixQuery\Annotation\SuffixQuery;
use Hyperf\Contract\StdoutLoggerInterface;

/**
 * @Aspect
 */
class SuffixQueryAspect extends AbstractAspect
{
    public $annotations = [
        SuffixQuery::class,
    ];

    /**
     * @Inject
     */
    protected StdoutLoggerInterface $logger;

    /**
     * @Inject
     */
    protected RequestInterface $request;

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        /** @var SuffixQuery $annotation */
        $annotation = $proceedingJoinPoint->getAnnotationMetadata()->method[SuffixQuery::class];

        $fields = explode(',', $annotation->fields);
        $requestData = $this->request->all();

        foreach ($fields as $field) {
            // 后缀查询仅支持 n 位且只能一个单号

            // 查询是否存在映射

            // 存在重新覆盖 request
        }
        return $proceedingJoinPoint->process();
    }

}
