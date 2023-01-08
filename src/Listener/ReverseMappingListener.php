<?php

declare(strict_types=1);

namespace Jeanile\SuffixQuery\Listener;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Database\Model\Events\Event;
use Hyperf\Database\Model\Events\Saved;
use Hyperf\Database\Model\Model;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Utils\Codec\Json;
use Jeanile\SuffixQuery\Event\ReverseMappingCreated;
use Jeanile\SuffixQuery\Event\ReverseMappingUpdated;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * @Listener
 */
class ReverseMappingListener implements ListenerInterface
{
    private LoggerInterface $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->logger = $container->get(StdoutLoggerInterface::class);
    }

    public function listen(): array
    {
        // TODO 此处自定义创建和更新事件继承 Created 和 Updated，后续根据业务需要继承，业务自行实现
        return [
            ReverseMappingCreated::class,
            ReverseMappingUpdated::class,
        ];
    }

    /**
     * @param ReverseMappingCreated|ReverseMappingUpdated $event
     */
    public function process(object $event)
    {
        if ($event instanceof Event) {
            $model = $event->getModel();
            $data = [];
            // 业务事件继承后，自行传递需要映射的字段，此处只对映射数据做处理，不做业务逻辑
            foreach ($model->getMappingColumns() as $mappingColumn) {
                $data[] = $model->getAttribute($mappingColumn);
            }
            // TODO 考虑是否抽象不同驱动
            $this->logger->info(sprintf("reverse mapping:%s", Json::encode($data)));
            $data && $model->save($data);
        }
    }

}
