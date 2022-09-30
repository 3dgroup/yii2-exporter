<?php

declare(strict_types=1);

namespace threedgroup\exporter;

use threedgroup\exporter\interfaces\Chunked;
use threedgroup\exporter\interfaces\FromArray;
use threedgroup\exporter\interfaces\FromActiveQuery;
use threedgroup\exporter\interfaces\WithHeader;
use threedgroup\exporter\interfaces\WithMapping;
use threedgroup\exporter\traits\Exportable;
use yii\db\ActiveQuery;

class Exporter
{
    private $exporter;

    public function processBatch(ActiveQuery $query, $fileResource)
    {
        if ($this->exporter instanceof Chunked) {
            foreach ($query->batch($this->exporter->chunkSize()) as $batch) {
                foreach ($batch as $batchRow) {
                    $this->processRow($batchRow, $fileResource);
                }
            }
        } else {
            foreach ($query->all() as $row) {
                $this->processRow($row, $fileResource);
            }
        }
    }

    public function processArray(array $data, $fileResource)
    {
        foreach ($data as $fields) {
            $this->processRow($fields, $fileResource);
        }
    }

    public function processRow($row, $fileResource)
    {
        if ($this->exporter instanceof WithMapping) {
            fputcsv($fileResource, $this->exporter->map($row));
        } elseif ($this->exporter instanceof FromActiveQuery) {
            fputcsv($fileResource, $row->getAttributes());
        } else {
            fputcsv($fileResource, $row);
        }
    }

    public function addHeader($fileResource)
    {
        fputcsv($fileResource, $this->exporter->headerRow());
    }

    private function setFileAndHeaders(string $fileName)
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $this->fileName($fileName) . '"');

        return fopen('php://output', 'wb');
    }

    public function download($exporter, string $fileName)
    {
        if (!in_array(Exportable::class, class_uses($exporter))) {
            throw new \Exception('Exporter class must implement ' . Exportable::class);
        }
        $this->exporter = $exporter;

        $fileResource = $this->setFileAndHeaders($fileName);

        if ($this->exporter instanceof WithHeader) {
            $this->addHeader($fileResource);
        }
        if ($this->exporter instanceof FromActiveQuery) {
            $this->processBatch($this->exporter->query(), $fileResource);
        } elseif ($this->exporter instanceof FromArray) {
            $this->processArray($this->exporter->getData(), $fileResource);
        }

        fclose($fileResource);
        exit;
    }

    private function fileName(string $fileName)
    {
        $fileName = str_replace(['.csv', '.xls'], '', $fileName);

        $startDate = $this->exporter->dateFrom ?? null;
        $endDate = $this->exporter->dateTo ?? null;

        if ($startDate) {
            $fileName .= ' [';
            $fileName .= $startDate->format('d-m-Y');
            $fileName .= ' until ';
            $fileName .= $endDate ? $endDate->format('d-m-Y') : (new \DateTimeImmutable())->format('d-m-Y');
            $fileName .= ']';
        }

        return $fileName . '.csv';
    }
}
