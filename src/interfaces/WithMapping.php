<?php

declare(strict_types=1);

namespace threedgroup\exporter\interfaces;

use yii\db\ActiveRecord;

interface WithMapping
{
    public function map(ActiveRecord $model): array;
}
