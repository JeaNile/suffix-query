<?php

declare(strict_types=1);

namespace Jeanile\SuffixQuery\Model;

use Hyperf\DbConnection\Model\Model;

/**
 * @property int $id
 * @property string $order_no
 * @property string $reverse_order_no
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ReverseMapping extends Model
{
    protected $table = 'reverse_mapping';

    protected $fillable = ['id', 'original_data', 'reverse_data', 'created_at', 'updated_at'];

    protected $casts = ['id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}
