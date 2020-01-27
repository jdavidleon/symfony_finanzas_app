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

    private $consolidated = [];
    /**
     * @var Spreadsheet
     */
    private $spreadsheet;

    /**
     * ConsumesResumeReportGenerator constructor.
     * @param Spreadsheet $spreadsheet
     * @param CreditCardConsumeExtractor $consumeExtractor
     */
    public function __construct(Spreadsheet $spreadsheet, CreditCardConsumeExtractor $consumeExtractor)
    {
        $this->spreadsheet = $spreadsheet;
        $this->consumeExtractor = $consumeExtractor;
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
        $activeSheet = 0;
        foreach ($consumes as $consume) {
            if (!$consume InstanceOf CreditCardConsume) {
                continue;
            }

            $sheet = $this->spreadsheet->setActiveSheetIndex($activeSheet);

            $this->setTableHeader($sheet, $consume);

            $payments = $this->consumeExtractor->extractPaymentListResumeByConsume($consume);
            $this->addPaymentsToSheet($sheet, $payments);

            $this->spreadsheet->createSheet();
            $activeSheet++;
        }

        $sheet = $this->spreadsheet->setActiveSheetIndex($activeSheet);
        $this->addConsolidatedSheet($sheet);

        $writer = new Xlsx($this->spreadsheet);
        $writer->save($temp_file);
    }

    /**
     * @param Worksheet $sheet
     * @param CreditCardConsume $consume
     * @throws Exception
     */
    private function setTableHeader(Worksheet $sheet, CreditCardConsume $consume)
    {
        $sheet->setTitle(substr(ucwords($consume->getDescription()), 0, 25));

        $sheet->getStyle('A:I')->getAlignment()->setHorizontal('center');
        $sheet->mergeCells('A1:I1');

        $sheet->setCellValue('A1',
            sprintf('%s (%s)', strtoupper($consume->getDescription()), $consume->getCode())
        );

        $sheet->setCellValue('A2', 'Fecha');
        $sheet->setCellValue('B2', $consume->getConsumeAt()->format('d/m/Y'));
        $sheet->setCellValue('C2', 'Deuda');
        $this->setMoneyValue($sheet, 'D2', $consume->getAmount());
        $sheet->setCellValue('E2', 'N° Cuotas');
        $sheet->setCellValue('F2', $consume->getDues());
        $sheet->setCellValue('G2', sprintf('Tasa de Interes: %s%%', $consume->getInterest()));
        $sheet->mergeCells('H2:I2');
        $sheet->setCellValue('H2', $consume->getCreditCardUser()->getFullName());

        $this->setColumnsRangeAutoSize($sheet, 'A', 'H');

        $sheet->setCellValue('A3', 'MES CUOTA');
        $sheet->setCellValue('B3', 'FECHA PAGO');
        $sheet->setCellValue('C3', 'N° CUOTA');
        $sheet->setCellValue('D3', 'DEUDA');
        $sheet->setCellValue('E3', 'INTERES');
        $sheet->setCellValue('F3', 'ABONO A CAPITAL');
        $sheet->setCellValue('G3', 'OTROS');
        $sheet->setCellValue('H3', 'VALOR A PAGAR');
        $sheet->setCellValue('I3', 'ESTADO');
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
            $payedAt = $payment->getPayedAt() ? $payment->getPayedAt()->format('Y-m-d') : 'N/A';
            $sheet->setCellValue('B' . $row, $payedAt);
            $sheet->setCellValue('C' . $row, $payment->getDueNumber());
            $this->setMoneyValue($sheet, 'D' . $row, $payment->getActualDebt());
            $this->setMoneyValue($sheet, 'E' . $row, $payment->getInterest());
            $this->setMoneyValue($sheet, 'F' . $row, $payment->getCapitalAmount());
            // Todo: Esto debe cambiar cuando se agreguen cargos adicionales al consumo. G.$row
            $sheet->setCellValue('G' . $row,0);
            $this->setMoneyValue($sheet, 'H' . $row, $payment->getTotalToPay());
            $sheet->setCellValue('I' . $row, strtoupper($payment->getStatus()));

            if (ConsumePaymentResume::STATUS_PAYED == $payment->getStatus()) {
                $sheet->getStyle('A' . $row . ':I' . $row)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('7ff779');
            }
            $row++;

            if (!isset($this->consolidated[$payment->getPaymentMonth()])) {
                $this->consolidated[$payment->getPaymentMonth()] = 0;
            }

            $this->consolidated[$payment->getPaymentMonth()] += $payment->getTotalToPay();
        }
        $this->setTableBorders($sheet, 'A1:I' . --$row);
    }

    /**
     * @param Worksheet $sheet
     * @throws Exception
     * @throws \Exception
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

            if ($month == $this->consumeExtractor->extractNextPaymentMonth()) {
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