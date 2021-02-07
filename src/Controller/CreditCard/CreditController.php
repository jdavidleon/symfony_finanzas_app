<?php

namespace App\Controller\CreditCard;

use App\Entity\CreditCard\CreditCardConsume;
use App\Entity\CreditCard\CreditCardUser;
use App\Extractor\CreditCard\CreditCardConsumeExtractor;
use App\Extractor\CreditCard\CreditCardExtractor;
use App\Form\Credit\CreditConsumeType;
use App\Repository\CreditCard\CreditCardUserRepository;
use App\Service\CreditCard\ConsumeResolver;
use App\Service\CreditCard\CreditCardConsumeProvider;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/credits")
 * */
class CreditController extends AbstractController
{
    /**
     * @Security("is_granted('ROLE_USER')")
     * @Route("/list", name="credit_list")
     * @param CreditCardConsumeProvider $consumeProvider
     * @param CreditCardExtractor $cardExtractor
     * @param ConsumeResolver $consumeResolver
     * @return Response
     * @throws Exception
     */
    public function index(
        CreditCardConsumeProvider $consumeProvider,
        CreditCardExtractor $cardExtractor,
        ConsumeResolver $consumeResolver
    ): Response
    {
        $creditCardConsumes = $consumeProvider->getByOwner($this->getUser());
        $creditCards = $cardExtractor->extractByOwner($this->getUser());
        $consumesCreated = $consumeProvider->getCreatedConsumeListByOwner($this->getUser());

        $creditCardUserRepo = $this->getDoctrine()->getRepository(CreditCardUser::class);
        $cardUsers = $this->getActivesCardUserList($creditCardUserRepo, $consumeProvider, $consumeResolver);

        $totalDebt = $consumeResolver->resolveTotalDebtOfConsumesArray($creditCardConsumes);

        return $this->render('credit/index.html.twig', [
            'credit_cards' => $creditCards,
            'consumes' => $creditCardConsumes,
            'card_users' => $cardUsers,
            'consumes_created' => $consumesCreated,
            'total_debt' => $totalDebt
        ]);
    }

    /**
     * @Route("/new", name="credit_new")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function createCreditConsume(Request $request)
    {
        $creditConsume = new CreditCardConsume();

        $form = $this->createForm(CreditConsumeType::class, $creditConsume, [
            'owner' => $this->getUser()
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($creditConsume);
            $em->flush();

            $this->addFlash('success', 'Crédito agregado');
            return $this->redirectToRoute('credit_new');
        }

        return $this->render('credit/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/credit-card-debt/{creditCard}", name="credit_card_debt")
     * @param CreditCardConsumeExtractor $creditCardConsumeExtractor
     * @param $creditCard
     */
    public function creditCardDetail(CreditCardConsumeExtractor $creditCardConsumeExtractor, $creditCard)
    {
        $repo = $this->getDoctrine()->getRepository(CreditCardConsume::class);
        $creditCardDebts = $repo->findByCreditCard($creditCard);

        $debtsByUser = $creditCardConsumeExtractor->extractActualDebt($creditCardDebts);

        dump($debtsByUser); die;

    }

    /**
     * @Route("/assignconsumepaydate/{consumeId}", name="establish_consume_paydate")
     * @param CreditCardConsume $consumeId
     */
    public function establishPayDateConsume(CreditCardConsume $consumeId)
    {

    }

    /**
     * @param CreditCardUserRepository $creditCardUserRepo
     * @param CreditCardConsumeProvider $consumeProvider
     * @param ConsumeResolver $consumeResolver
     * @return CreditCardUser[]|array
     * @throws Exception
     */
    private function getActivesCardUserList(CreditCardUserRepository $creditCardUserRepo, CreditCardConsumeProvider $consumeProvider, ConsumeResolver $consumeResolver): array
    {
        return array_filter(
            $creditCardUserRepo->getByOwner($this->getUser(), true),
            function (CreditCardUser $cardUser) use ($consumeProvider, $consumeResolver) {
                $consumesByUser = $consumeProvider->getActivesByCardUser($cardUser);
                $totalDebt = $consumeResolver->resolveTotalDebtOfConsumesArray($consumesByUser);
                return 0 < $totalDebt;
            }
        );

    }

}
