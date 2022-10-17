<?php

declare(strict_types=1);

namespace Jeanile\SuffixQuery\Aspect;

use _PHPStan_acbb55bae\Psr\Http\Message\RequestInterface;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Di\Aop\AroundInterface;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Jeanile\SuffixQuery\Annotation\SuffixQuery;
use Hyperf\Contract\StdoutLoggerInterface;

/**
 * @Aspect
 */
class SuffixQueryAspect extends AbstractAspect
{
    public array $annotations = [
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
            if (empty($requestData[$field]) || strlen($requestData[$field]) != 4 || count(explode(',', $requestData[$field])) > 1) {
                continue;
            }

            // 查询是否存在映射
            $datas = $this->dataMappingRepository->getDatasByReverseDatas(strrev($requestData[$field]));

            // 存在
            if ($datas->isNotEmpty()) {
                $requestData[$field] = $datas->implode('order_no', ',');
                Context::set('http.request.parsedData', $requestData);
            }
        }

        // $req = ApplicationContext::getContainer()->get(Request::class);
        // $req->call('storeParsedData', )
        // $this->request->order = 1;

        // $setRouter = function (string $key) {
        //     $this->routers[$key] = 123;
        // };
        //
        // $setRouter->call($factory, 'http', $fakeRouter);

        // echo PHP_EOL;var_dump($needDeal);echo PHP_EOL;die();

        return $proceedingJoinPoint->process();
    }

    private function buildRequest($fd, $reactorId, $data)
    {
        (new \Hyperf\HttpMessage\Server\Request())->withAttribute('order', $fd)
            ->withAttribute('fromId', $reactorId)
            ->withAttribute('data', $data)
            ->withAttribute('request_id', $data['id'] ?? null)
            ->withParsedBody($data['params'] ?? '');
    }

}
