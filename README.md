# suffix-query

## 概述
该项目是**基于 Hyperf 框架**简单实现的**后缀查询组件**，主要用于实现类似于 `*` 的后缀查询，例如手机尾号后 n 位查询等场景，如果是简单的 `%3761` 无法命中模糊索引，因此该包将查询结果逆序，达到”后缀索引“的效果。

## 安装

```shell
$ composer require "jeanile/suffix-query"
```

## 使用

* PHP 7.4+
* Swoole 4.4LTS+
* Redis 3.2+
* Hyperf 2.2+

### 执行迁移

```shell
$ php bin/hyperf.php migrate --path=./vendor/jeanile/suffix-query/migrations/2022_09_27_144249_create_reverse_mapping_table.php
```

### 模型

模型加入 `getReverseMappingCreatedColumns()` 方法，代表创建时需要逆序的字段
`getReverseMappingUpdatedColumns()` 方法，代表更新时需要逆序的字段，没有则可不添加

```php
<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

class PurchaseOrder extends Model
{
    protected $table = 'purchase_order';
    ..
    ..
    
    // 创建时需要逆序映射的字段
    public function getReverseMappingCreatedColumns(): array
    {
        return ['purchase_order_no'];
    }
    
    // 更新时需要逆序映射的字段，没有可不写该方法
    public function getReverseMappingUpdatedColumns(): array
    {
        return ['purchase_order_no'];
    }
}
```

### 插入数据时映射逆序字段

在创建/更新数据的地方增加触发 ReverseMappingCreated/ReverseMappingUpdated 事件

```php

<?php

declare(strict_types=1);

namespace App\Service;

use Jeanile\SuffixQuery\Event\ReverseMappingCreated;
use Psr\EventDispatcher\EventDispatcherInterface;

class PurchaseOrderService
{
    /**
     * @Inject
     */
    protected EventDispatcherInterface $eventDispatcher;
    
    /**
     * @Transactional
     */
    public function create(array $data): PurchaseOrder
    {
        // 业务处理
        
        $purchaseOrder = Purchase::query()->create($data);
        // 派发逆序映射事件
        $this->eventDispatcher->dispatch(new ReverseMappingCreated($purchaseOrder));
        return $purchaseOrder;
    }
   
}
```

### 查询数据时查询逆序字段

在方法前加入注解 `@SuffixQuery(fields="order_no")`;
如果有需要支持多个字段，则按逗号分开  `@SuffixQuery(fields="order_no, express_no")`

```php

<?php

declare(strict_types=1);

namespace App\Service;

use App\Controller\AbstractController;
use App\Middleware\TokenMiddleware;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Jeanile\SuffixQuery\Annotation\SuffixQuery;

/**
 * @Middlewares({
 *     @Middleware(TokenMiddleware::class),
 * })
 * @Controller(prefix="purchase_order")
 */
class PurchaseOrderController extends AbstractController
{

    /**
     * 列表接口.
     * @GetMapping(path="list")
     * @SuffixQuery(fields="order")
     */
    public function list(array $data): array
    {
        // 业务处理
    }
    
   
}
```


### 历史数据处理
```shell
$ php bin/hyperf.php mapping:reverse [模型类名] [指定 id 之后开始] [过滤数据]
```

