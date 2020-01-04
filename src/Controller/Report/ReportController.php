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
     * @Route("/default/{cardUser}")
     * @param CreditCardUser $cardUser
     * @param ReportGenerator $reportGenerator
     * @return BinaryFileResponse
     * @throws \Exception
     */
    public function DefaultController(CreditCardUser $cardUser, ReportGenerator $reportGenerator)
    {
        $fileName = sprintf('Deuda_%s.xlsx', $cardUser->getFullName());
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);

        $reportGenerator->generateByCardUser($cardUser, $temp_file);
        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_ATTACHMENT);
    }
}