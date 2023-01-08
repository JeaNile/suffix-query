<?php

declare(strict_types=1);

namespace Jeanile\SuffixQuery\Aspect;

use Hyperf\Context\Context;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\AroundInterface;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Jeanile\SuffixQuery\Annotation\SuffixQuery;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\HttpServer\Contract\RequestInterface;
use Jeanile\SuffixQuery\Model\ReverseMapping;
use Jeanile\SuffixQuery\ReverseMappingServer;


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


    /**
     * @Inject
     */
    protected ReverseMappingServer $reverseMappingServer;

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        /** @var SuffixQuery $annotation */
        $annotation = $proceedingJoinPoint->getAnnotationMetadata()->method[SuffixQuery::class];

        $fields = explode(',', $annotation->fields);
        $requestData = $this->request->all();

        foreach ($fields as $field) {
            if (empty($field)) {
                break;
            }

            // 查询是否存在映射
            $reverseMappings = $this->reverseMappingServer->get($field, $annotation->limit);

            // 存在重新覆盖 request
            if ($reverseMappings->isNotEmpty()) {
                $requestData[$field] = implode(',', array_column($reverseMappings->toArray(), 'original_data'));
                // 覆盖 request
                Context::set('http.request.parsedData', $requestData);
            }
        }
        return $proceedingJoinPoint->process();
    }

}
