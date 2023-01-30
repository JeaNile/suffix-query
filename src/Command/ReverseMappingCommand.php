<?php

declare(strict_types=1);

namespace Jeanile\SuffixQuery\Command;

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Database\Model\Model;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Redis\Redis;
use Hyperf\Utils\Coroutine\Concurrent;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Jeanile\SuffixQuery\Exception\ReverseMappingException;
use Jeanile\SuffixQuery\ReverseMappingServer;

/**
 * @Command
 */
class ReverseMappingCommand extends HyperfCommand
{
    protected ContainerInterface $container;
    protected ReverseMappingServer $reverseMappingServer;


    public function __construct(ContainerInterface $container, ReverseMappingServer $reverseMappingServer)
    {
        $this->container = $container;
        $this->reverseMappingServer = $reverseMappingServer;
        parent::__construct('mapping:reverse');
    }


    public function configure()
    {
        parent::configure();
        $this->setDescription('对历史数据逆向进行映射');
    }


    public function handle()
    {
        $redis = $this->container->get(Redis::class);
        $models = $this->getNeedMappingModel();
        $concurrent = new Concurrent(4);
        $startId = $this->getStartId();
        foreach ($models as $model) {
            $concurrent->create(function () use ($model, $redis, $startId) {
                $key = sprintf('mapping:%s', $model);
                /** @var Model $modelInstance */
                $modelInstance = new $model();
                $modelInstance
                    ->query()
                    ->where('id', '>', $redis->get($key) ?: $startId)
                    ->chunkById(100, function ($items) use ($redis, $key) {
                        Db::transaction(function () use ($items) {
                            $mappings = [];
                            foreach ($items as $item) {
                                $reverseMappingColumns = $item->getReverseMappingCreatedColumns();

                                // 存在更新字段则合并
                                if (method_exists($item, 'getReverseMappingUpdatedColumns')) {
                                    $reverseMappingColumns[] = $item->getReverseMappingUpdatedColumns();
                                }
                                foreach ($reverseMappingColumns ?? [] as $column) {
                                    $data = $item->getOriginal($column);

                                    // 过滤指定历史数据
                                    if (in_array($data, $this->getFilterData())) {
                                        continue;
                                    }

                                    /* @var Model $item */
                                    $mappings[] = $data;
                                }
                            }

                            $mappings && $this->reverseMappingServer->batchInsert($mappings);
                        });

                        $redis->set($key, $items->last()->id, ['ex' => 86400]);
                    });
            });
        }
    }

    protected function getArguments(): array
    {
        return [
            ['models', InputArgument::REQUIRED, '模型'],
            ['start_id', InputArgument::OPTIONAL, '开始的 id', []],
            ['filter', InputArgument::OPTIONAL, '需要过滤的数据', []],
        ];
    }

    protected function getNeedMappingModel(): array
    {
        $models = $this->input->getArgument('models');
        if (! $models) {
            throw new ReverseMappingException('非法参数');
        }
        $needMappingModels = [];
        // 校验是否存在 model
        foreach (explode(',', $models) as $model) {
            $model = sprintf('App\\Model\\%s', $model);
            echo PHP_EOL;var_dump($model);echo PHP_EOL;
            if (! class_exists($model)) {
                throw new ReverseMappingException('非法参数');
            }
            $needMappingModels[] = $model;
        }
        return $needMappingModels;
    }

    protected function getStartId(): int
    {
        $startId = $this->input->getArgument('start_id');
        if (! $startId) {
            return 0;
        }
        return (int) $startId;
    }

    protected function getFilterData(): array
    {
        $filter = $this->input->getArgument('filter');
        return $filter ? explode(',', $filter) : [];
    }
}
