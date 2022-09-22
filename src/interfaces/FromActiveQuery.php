<?php

declare(strict_types=1);

namespace threedgroup\exporter\interfaces;

use yii\db\ActiveQuery;

interface FromActiveQuery
{
    public function query(): ActiveQuery;
}
