<?php

declare(strict_types=1);

namespace threedgroup\exporter\interfaces;

interface WithHeader
{
    public function headerRow(): array;
}
