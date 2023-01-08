<?php

declare(strict_types=1);

namespace Jeanile\SuffixQuery\Command;

use Carbon\Carbon;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Database\Model\Model;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Redis\Redis;
use Hyperf\Utils\Coroutine\Concurrent;
use Psr\Container\ContainerInterface;

/**
 * @Command
 */
class ReverseMappingCommand extends HyperfCommand
{
    protected ContainerInterface $container;


    public function __construct(
        ContainerInterface $container,
    ) {
        $this->container = $container;

        parent::__construct('mapping:order');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('对数据进行映射');
    }

    public function handle()
    {
        $redis = $this->container->get(Redis::class);
        $models = $this->needMappingModel();
        $concurrent = new Concurrent(4);
        $startId = 0;
        foreach ($models as $model) {
            $concurrent->create(function () use ($model, $redis, $startId) {
                $key = sprintf('mapping:%s', $model);
                /** @var Model $modelInstance */
                $modelInstance = new $model();
                $startTime = Carbon::now()->subMonth()->toDateTimeString();
                $endTime = Carbon::now()->toDateTimeString();
                $modelInstance
                    ->query()
                    ->where('id', '>', $redis->get($key) ?: $startId)
                    ->whereBetween('created_at', [$startTime, $endTime])
                    ->chunkById(100, function ($items) use ($redis, $key) {
                        Db::transaction(function () use ($items) {
                            $mappings = [];
                            foreach ($items as $item) {
                                foreach ($item->getMappingColumns() ?? [] as $column) {
                                    // 过滤指定历史数据
                                    $data = $item->getOriginal($column);

                                    if (in_array($data, $this->getFilterData())) {
                                        continue;
                                    }

                                    /* @var Model $item */
                                    $mappings[] = $data;
                                }
                            }

                            $mappings && $this->orderMappingService->batchInsert($mappings);
                        });
                        $redis->set($key, $items->last()->id, ['ex' => 86400]);
                    });
            });
        }
    }

    public function getFilterData(): array
    {
        return [];
    }

    public function needMappingModel(): array
    {
        return [

        ];
    }
}
