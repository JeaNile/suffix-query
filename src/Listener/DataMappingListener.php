<?php

declare(strict_types=1);

namespace Jeanile\SuffixQuery\Listener;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Database\Model\Events\Event;
use Hyperf\Database\Model\Events\Saved;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Utils\Codec\Json;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * @Listener
 */
class DataMappingListener implements ListenerInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->logger = $container->get(StdoutLoggerInterface::class);
    }

    public function listen(): array
    {
        // TODO 此处自定义创建和更新事件继承 Created 和 Updated，后续根据业务需要继承，业务自行实现
        return [
            DataMappingCreated::class,
            DataMappingUpdated::class,
        ];
    }

    public function process(object $event)
    {
        if ($event instanceof Event) {
            $model = $event->getModel();
            $orders = [];
            // 业务事件继承后，自行传递需要映射的字段，此处只对映射数据做处理，不做业务逻辑
            foreach ($model->getMappingColumns() as $mappingColumn) {
                $orders[] = $model->getAttribute($mappingColumn);
            }
            // TODO 考虑是否抽象不同驱动
            $this->logger->info(sprintf("data mapping:%s", Json::encode($orders)));
            $orders && $model->save($orders);
        }
    }

}
