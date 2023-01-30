<?php

declare(strict_types=1);

namespace Jeanile\SuffixQuery\Listener;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Utils\Codec\Json;
use Jeanile\SuffixQuery\Event\ReverseMappingCreated;
use Jeanile\SuffixQuery\Event\ReverseMappingUpdated;
use Jeanile\SuffixQuery\ReverseMappingServer;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * @Listener
 */
class ReverseMappingListener implements ListenerInterface
{
    private LoggerInterface $logger;
    private ReverseMappingServer $reverseMappingServer;

    public function __construct(ContainerInterface $container)
    {
        $this->logger = $container->get(StdoutLoggerInterface::class);
        $this->reverseMappingServer = $container->get(ReverseMappingServer::class);
    }

    public function listen(): array
    {
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
        if ($event instanceof ReverseMappingCreated || $event instanceof ReverseMappingUpdated) {
            $model = $event->model;
            $columns = sprintf('getReverseMapping%sColumns', $event instanceof ReverseMappingCreated ? 'Created' : 'Updated');

            $data = [];
            foreach ($model->{$columns}() as $mappingColumn) {
                $model->getAttribute($mappingColumn) && $data[] = $model->getAttribute($mappingColumn);
            }

            $this->logger->info(sprintf("reverse mapping:%s", Json::encode($data)));

            $data && $this->reverseMappingServer->batchInsert($data);
        }
    }

}
