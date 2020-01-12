<?php


namespace App\Controller\Report;


use App\Entity\CreditCard\CreditCardUser;
use App\Service\Report\ReportGenerator;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/report")
 * */
class ReportController extends AbstractController
{
    /**
     * @Route("/resume/carduser/{cardUser}", name="resume_by_card_user")
     * @param CreditCardUser $cardUser
     * @param ReportGenerator $reportGenerator
     * @return BinaryFileResponse
     * @throws \Exception
     */
    public function cardUserResumeConsumes(CreditCardUser $cardUser, ReportGenerator $reportGenerator)
    {
        $fileName = sprintf('Deuda_%s_%s.xlsx', $cardUser->getFullName(), date('Y-M-d'));
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);

        $reportGenerator->generateResumeByCardUser($cardUser, $temp_file);
        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_ATTACHMENT);
    }
}