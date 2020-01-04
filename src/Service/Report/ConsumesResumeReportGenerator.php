<?php


namespace App\Service\Report;


use App\Entity\CreditCard\CreditCardConsume;
use App\Extractor\CreditCard\CreditCardConsumeExtractor;
use App\Model\Payment\ConsumePaymentResume;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ConsumesResumeReportGenerator
{
    /**
     * @var CreditCardConsumeExtractor
     */
    private $consumeExtractor;

    private $actualPaymentMonth;

    private $consolidated = [];

    /**
     * ConsumesResumeReportGenerator constructor.
     * @param CreditCardConsumeExtractor $consumeExtractor
     * @throws \Exception
     */
    public function __construct(CreditCardConsumeExtractor $consumeExtractor)
    {
        $this->consumeExtractor = $consumeExtractor;
        $this->actualPaymentMonth = $this->consumeExtractor->extractNextPaymentMonth();
    }

    /**
     * @param CreditCardConsume[] $consumes
     * @param $temp_file
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \Exception
     */
    public function generateByConsumesArray(array $consumes, $temp_file)
    {
        $spreadsheet = new Spreadsheet();
        $activeSheet = 0;
        foreach ($consumes as $consume) {
            if (!$consume InstanceOf CreditCardConsume) {
                continue;
            }

            $sheet = $spreadsheet->setActiveSheetIndex($activeSheet);

            $this->setTableHeader($sheet, $consume);

            $payments = $this->consumeExtractor->extractPaymentListByConsume($consume);
            $this->addPaymentsToSheet($sheet, $payments);

            $spreadsheet->createSheet();
            $activeSheet++;
        }

        // Activamos la ultima hoja para usarla en el consolidado
        $sheet = $spreadsheet->setActiveSheetIndex($activeSheet);
        $this->addConsolidatedSheet($sheet);

        $writer = new Xlsx($spreadsheet);
        $writer->save($temp_file);
    }

    /**
     * @param Worksheet $sheet
     * @param CreditCardConsume $consume
     * @throws Exception
     */
    private function setTableHeader(Worksheet $sheet, CreditCardConsume $consume)
    {
        $sheet->setTitle(ucwords($consume->getDescription()));

        $sheet->getStyle('A:H')->getAlignment()->setHorizontal('center');
        $sheet->mergeCells('A1:H1');

        $sheet->setCellValue('A1',
            sprintf('%s (%s)', strtoupper($consume->getDescription()), $consume->getCode())
        );

        $sheet->setCellValue('A2', 'Fecha');
        $sheet->setCellValue('B2', $consume->getConsumeAt()->format('d/m/Y'));
        $sheet->setCellValue('C2', 'Deuda');
        $this->setMoneyValue($sheet, 'D2', $consume->getAmount());
        $sheet->setCellValue('E2', 'N° Cuotas');
        $sheet->setCellValue('F2', $consume->getDues());
        $sheet->setCellValue('G2', 'Tasa de Interes');
        $sheet->setCellValue('H2', $consume->getInterest());

        $this->setColumnsRangeAutoSize($sheet, 'A', 'H');

        $sheet->setCellValue('A3', 'MES');
        $sheet->setCellValue('B3', 'N° CUOTA');
        $sheet->setCellValue('C3', 'DEUDA');
        $sheet->setCellValue('D3', 'INTERES');
        $sheet->setCellValue('E3', 'ABONO A CAPITAL');
        $sheet->setCellValue('F3', 'OTROS');
        $sheet->setCellValue('G3', 'VALOR A PAGAR');
        $sheet->setCellValue('H3', 'ESTADO');
    }

    /**
     * @param Worksheet $sheet
     * @param ConsumePaymentResume[] $payments
     * @throws Exception
     * @throws \Exception
     */
    private function addPaymentsToSheet(Worksheet $sheet, array $payments)
    {
        $row = 4;
        foreach ($payments as $payment) {
            $this->setMoneyValue($sheet, 'A' . $row, $payment->getPaymentMonth());
            $sheet->setCellValue('B' . $row, $payment->getDueNumber());
            $this->setMoneyValue($sheet, 'C' . $row, $payment->getActualDebt());
            $this->setMoneyValue($sheet, 'D' . $row, $payment->getInterest());
            $this->setMoneyValue($sheet, 'E' . $row, $payment->getCapitalAmount());
            $sheet->setCellValue('F' . $row,
                0); // Todo: Esto debe cambiar cuando se agreguen cargos adicionales al consumo
            $this->setMoneyValue($sheet, 'G' . $row, $payment->getTotalToPay());
            $sheet->setCellValue('H' . $row, strtoupper($payment->getStatus()));

            if (ConsumePaymentResume::STATUS_PAYED == $payment->getStatus()) {
                $sheet->getStyle('A' . $row . ':H' . $row)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('7ff779');
            }
            $row++;

            if (!isset($this->consolidated[$payment->getPaymentMonth()])) {
                $this->consolidated[$payment->getPaymentMonth()] = 0;
            }

            $this->consolidated[$payment->getPaymentMonth()] += $payment->getTotalToPay();
        }
        $this->setTableBorders($sheet, 'A1:H' . --$row);
    }

    /**
     * @param Worksheet $sheet
     * @throws Exception
     */
    private function addConsolidatedSheet(Worksheet $sheet)
    {
        $sheet->getStyle('A:D')->getAlignment()->setHorizontal('center');
        $this->setColumnsRangeAutoSize($sheet, 'A', 'D');

        $sheet->setTitle('CONSOLIDADO');
        $sheet->setCellValue('A1', 'MES');
        $sheet->setCellValue('B1', 'VALOR');
        $sheet->setCellValue('C1', 'OTROS');
        $sheet->setCellValue('D1', 'TOTAL');

        $row = 2;
        ksort($this->consolidated);
        foreach ($this->consolidated as $month => $amount) {
            $sheet->setCellValue('A' . $row, $month);
            $this->setMoneyValue($sheet, 'B' . $row, $amount);
            $sheet->setCellValue('C' . $row, 0);
            $this->setMoneyValue($sheet, 'D' . $row, $amount);

            if ($month == $this->actualPaymentMonth) {
                $sheet->getStyle('A' . $row . ':D' . $row)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('f6f925');
            }
            $row++;
        }

        $this->setTableBorders($sheet, 'A1:D' . --$row);
    }

    /**
     * @param Worksheet $sheet
     * @param $cell
     * @param $value
     * @throws Exception
     */
    private function setMoneyValue(Worksheet $sheet, $cell, $value)
    {
        $sheet->getCell($cell)->setValue($value)->getStyle()->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
    }

    /**
     * @param Worksheet $sheet
     * @param $start
     * @param $end
     * @return void
     */
    private function setColumnsRangeAutoSize(Worksheet $sheet, $start, $end)
    {
        foreach (range($start, $end) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }

    /**
     * @param Worksheet $sheet
     * @param $pCellCoordinate
     * @throws Exception
     */
    private function setTableBorders(Worksheet $sheet, $pCellCoordinate): void
    {
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '404040'],
                ],
            ],
        ];

        $sheet->getStyle($pCellCoordinate)->applyFromArray($styleArray);
    }
}