<?php


namespace App\Service\Report;


use App\Entity\CreditCard\CreditCardUser;
use App\Extractor\CreditCard\CreditCardConsumeExtractor;
use App\Service\CreditCard\CreditCardConsumeProvider;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportGenerator
{
    /**
     * @var Spreadsheet
     */
    private $spreadsheet;
    /**
     * @var CreditCardConsumeProvider
     */
    private $cardConsumeProvider;
    /**
     * @var CreditCardConsumeExtractor
     */
    private $cardConsumeExtractor;

    /**
     * ReportGenerator constructor.
     * @param CreditCardConsumeExtractor $cardConsumeExtractor
     * @param CreditCardConsumeProvider $cardConsumeProvider
     */
    public function __construct(CreditCardConsumeExtractor $cardConsumeExtractor, CreditCardConsumeProvider $cardConsumeProvider)
    {
        $this->spreadsheet = new Spreadsheet();
        $this->cardConsumeProvider = $cardConsumeProvider;
        $this->cardConsumeExtractor = $cardConsumeExtractor;
    }

    /**
     * @param CreditCardUser $cardUser
     * @param $temp_file
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \Exception
     */
    public function generateByCardUser(CreditCardUser $cardUser, $temp_file)
    {
        $consumes = $this->cardConsumeProvider->getByCardUser($cardUser);

        $activeSheet = 0;
        $consolidado = [];
        foreach ($consumes as $consume) {
            $this->spreadsheet->setActiveSheetIndex($activeSheet);
            $sheet = $this->spreadsheet->getActiveSheet();

            $sheet->setTitle(ucfirst($consume->getDescription()));

            $sheet->getStyle('A:H')->getAlignment()->setHorizontal('center');
            $sheet->mergeCells('A1:H1');
            $sheet->setCellValue('A1', sprintf('%s (%s)', strtoupper($consume->getDescription()), $consume->getCode()));

            $sheet->setCellValue('A2', 'Fecha');
            $sheet->setCellValue('B2', $consume->getConsumeAt()->format('d/m/Y'));
            $sheet->setCellValue('C2', 'Deuda');
            $this->setMoneyValue($sheet, 'D2', $consume->getAmount());
            $sheet->setCellValue('E2', 'N° Cuotas');
            $sheet->setCellValue('F2', $consume->getDues());
            $sheet->setCellValue('G2', 'Tasa de Interes');
            $sheet->setCellValue('H2', sprintf('%d%%', $consume->getInterest()));

            $sheet->getColumnDimension('A')->setAutoSize(true);
            $sheet->getColumnDimension('B')->setAutoSize(true);
            $sheet->getColumnDimension('C')->setAutoSize(true);
            $sheet->getColumnDimension('D')->setAutoSize(true);
            $sheet->getColumnDimension('E')->setAutoSize(true);
            $sheet->getColumnDimension('F')->setAutoSize(true);
            $sheet->getColumnDimension('G')->setAutoSize(true);
            $sheet->getColumnDimension('H')->setAutoSize(true);

            $sheet->setCellValue('A3', 'MES');
            $sheet->setCellValue('B3', 'N° CUOTA');
            $sheet->setCellValue('C3', 'DEUDA');
            $sheet->setCellValue('D3', 'INTERES');
            $sheet->setCellValue('E3', 'ABONO A CAPITAL');
            $sheet->setCellValue('F3', 'OTROS');
            $sheet->setCellValue('G3', 'VALOR A PAGAR');
            $sheet->setCellValue('H3', 'ESTADO');

            $payments = $this->cardConsumeExtractor->extractPaymentListByConsume($consume);

            $row = 4;
            foreach ($payments as $payment) {
                $this->setMoneyValue($sheet, 'A'.$row, $payment->getPaymentMonth());
                $sheet->setCellValue('B'.$row, $payment->getDueNumber());
                $this->setMoneyValue($sheet, 'C'.$row, $payment->getActualDebt());
                $this->setMoneyValue($sheet, 'D'.$row, $payment->getInterest());
                $this->setMoneyValue($sheet, 'E'.$row, $payment->getCapitalAmount());
                $sheet->setCellValue('F'.$row, 0); // Todo: Esto debe cambiar cuando se agreguen cargos adicionales al consumo
                $this->setMoneyValue($sheet, 'G'.$row, $payment->getTotalToPay());
                $sheet->setCellValue('H'.$row, strtoupper($payment->getStatus()));
                $row++;

                if (!isset($consolidado[$payment->getPaymentMonth()])) {
                    $consolidado[$payment->getPaymentMonth()] = 0;
                }

                $consolidado[$payment->getPaymentMonth()] += $payment->getTotalToPay();
            }
            $this->spreadsheet->createSheet();
            $activeSheet++;
        }

        $sheet = $this->spreadsheet->setActiveSheetIndex($activeSheet);
        $sheet->getStyle('A:D')->getAlignment()->setHorizontal('center');
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->setTitle('CONSOLIDADO');
        $sheet->setCellValue('A1', 'MES');
        $sheet->setCellValue('B1', 'VALOR');
        $sheet->setCellValue('C1', 'OTROS');
        $sheet->setCellValue('D1', 'TOTAL');

        $row = 2;
        ksort($consolidado);
        foreach ($consolidado as $month => $amount) {
            $sheet->setCellValue('A'.$row, $month);
            $this->setMoneyValue($sheet, 'B'.$row, $amount);
            $sheet->setCellValue('C'.$row, 0);
            $this->setMoneyValue($sheet, 'D'.$row, $amount);
            $row++;
        }

        $writer = new Xlsx($this->spreadsheet);

        $writer->save($temp_file);
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
}