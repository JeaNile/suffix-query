<?php

declare(strict_types=1);

namespace Jeanile\SuffixQuery\Listener;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Database\Model\Events\Event;
use Hyperf\Database\Model\Events\Saved;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;

/**
 * @Listener
 */
class DataMappingListener implements ListenerInterface
{
    // private StdoutLoggerInterface $logger;


    // public function __construct(
    //     StdoutLoggerInterface $logger,
    // ) {
    //     $this->logger = $logger;
    // }

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
            $orders && $model->save($orders);
        }
    }

}
