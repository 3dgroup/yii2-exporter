<?php

declare(strict_types=1);

namespace threedgroup\exporter\interfaces;

interface Chunked
{
    public function chunkSize(): int;
}
