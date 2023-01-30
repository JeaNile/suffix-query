<?php

namespace Jeanile\SuffixQuery;

use Carbon\Carbon;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\Codec\Json;
use Jeanile\SuffixQuery\Model\ReverseMapping;
use Hyperf\Redis\Redis;

class ReverseMappingServer
{
    /**
     * @Inject
     */
    protected StdoutLoggerInterface $logger;

    /**
     * @Inject
     */
    protected Redis $redis;

    public function get(string $reverseData, int $limit = 20)
    {
        return ReverseMapping::query()
            ->where('reverse_data', 'like', sprintf('%s%%', strrev($reverseData)))
            ->limit($limit)
            ->orderBy('id', 'desc')
            ->get();
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

            $redisKey = 'reverse_mapping:' . $order;
            // 判断如果存在 redis 中则不需要再次插入
            if (! $this->redis->set($redisKey, '1', ['NX', 'EX' => 5])) {
                $this->logger->warning(sprintf('订单映射重复:%s', $order));
                continue;
            }

            $insertData[] = [
                'original_data' => $order,
                'reverse_data' => strrev($order),
                'created_at' => $now->toDateTimeString(),
                'updated_at' => $now->toDateTimeString(),
            ];
        }
        if (empty($insertData)) {
            return true;
        }
        $this->logger->info(sprintf('订单映射:%s', Json::encode($insertData)));
        return ReverseMapping::query()->insert($insertData);
    }
}
