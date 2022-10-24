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
        return [
            Saved::class,
        ];
    }

    public function process(object $event)
    {
        if ($event instanceof Event) {
            $model = $event->getModel();
            $orders = [];
            foreach ($model->getMappingColumns() as $mappingColumn) {
                $orders[] = $model->getAttribute($mappingColumn);
            }
            $this->logger->info(sprintf("data mapping:%s", Json::encode($orders)));
            $orders && $model->save($orders);
        }
    }

}
