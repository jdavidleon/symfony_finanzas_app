<?php


namespace App\Service\Report;


use App\Entity\CreditCard\CreditCardUser;
use App\Service\CreditCard\CreditCardConsumeProvider;
use Exception;

class ReportGenerator
{
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
        $consumes = $this->cardConsumeProvider->getAllByCardUser($cardUser, null,true);

        $this->resumeReportGenerator->generateByConsumesArray($consumes, $temp_file);
    }
}