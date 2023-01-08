<?php

namespace Jeanile\SuffixQuery;

use Carbon\Carbon;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\Codec\Json;
use Jeanile\SuffixQuery\Model\ReverseMapping;

class ReverseMappingServer
{
    /**
     * @Inject
     */
    protected StdoutLoggerInterface $logger;

    public function get(string $reverseData, int $limit = 20)
    {
        return ReverseMapping::query()
            // ->where('reverse_data', 'like', sprintf('%s%%', strrev($reverseOrderNo)))
            ->where('reverse_data', $reverseData)
            ->limit($limit)
            ->orderBy('id', 'desc')
            ->get();

        // if ($reverseMappings->isNotEmpty()) {
        //     $requestData[$field] = implode(',', array_column($reverseMappings->toArray(), 'original_data'));
        //     // 覆盖 request
        //     Context::set('http.request.parsedData', $requestData);
        // }
    }

    public function getOriginalData(array $data, $isUniq = false): array
    {
        // 返回 ReverseMapping 的 original_data 并过滤重复
        return ReverseMapping::query()
            ->whereIn('original_data', $data)
            ->pluck('original_data')
            ->toArray();
    }

    public function batchInsert(array $orders): bool
    {
        $orders = array_unique(array_filter($orders));

        // 过滤表中已存在的订单号
        if ($orderMappings = $this->getOriginalData($orders)) {
            $orders = array_diff($orders, $orderMappings);
        }

        $insertData = [];
        $now = new Carbon();
        foreach ($orders as $order) {
            $insertData[] = [
                'order_no' => $order,
                'reverse_order_no' => strrev($order),
                'created_at' => $now->toDateTimeString(),
                'updated_at' => $now->toDateTimeString(),
            ];
        }
        $this->logger->info(sprintf('订单映射:%s', Json::encode($insertData)));
        return ReverseMapping::query()->insert($insertData);
    }
}
