<?php


namespace App\Service\Report;


use App\Entity\CreditCard\CreditCardUser;
use App\Service\CreditCard\CreditCardConsumeProvider;
use Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

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
     * @var ConsumesResumeReportGenerator
     */
    private $resumeReportGenerator;

    /**
     * ReportGenerator constructor.
     * @param ConsumesResumeReportGenerator $resumeReportGenerator
     * @param CreditCardConsumeProvider $cardConsumeProvider
     */
    public function __construct(ConsumesResumeReportGenerator $resumeReportGenerator, CreditCardConsumeProvider $cardConsumeProvider)
    {
        $this->spreadsheet = new Spreadsheet();
        $this->cardConsumeProvider = $cardConsumeProvider;
        $this->resumeReportGenerator = $resumeReportGenerator;
    }

    /**
     * @param CreditCardUser $cardUser
     * @param $temp_file
     * @throws Exception
     */
    public function generateResumeByCardUser(CreditCardUser $cardUser, $temp_file)
    {
        $consumes = $this->cardConsumeProvider->getAllByCardUser($cardUser);

        $this->resumeReportGenerator->generateByConsumesArray($consumes, $temp_file);
    }
}