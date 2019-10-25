<?php
/**
 * Created by PhpStorm.
 * User: IT OPTIME
 * Date: 24/01/2019
 * Time: 11:03 AM
 */

namespace App\Controller\Personal;


use App\Entity\Personal\Egress;
use App\Entity\Personal\Entry;
use App\Form\Personal\EgressType;
use App\Form\Personal\EntryType;
use App\Service\Personal\BalanceCalculations;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/*TODO: Trabajar en las vistas*/
/**
 *
 * @Route("/entries")
 *
 * */
class PersonalController extends AbstractController
{

    /**
     * @throws \Exception
     * @Route("/list", name="list-entry")
     */
    public function EntryList()
    {
        $repo = $this->getDoctrine()->getRepository(Entry::class);

        $entries = $repo->findBy([
           'user' => $this->getUser(),
           'deletedAt' => null
        ]);

        return $this->render('::base.html.twig', [
            'entries' => $entries
        ]);
    }

    /**
     * @param Request $request
     * @param BalanceCalculations $balanceCalculations
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     * @Route("/new", name="new-entry")
     */
    public function newEntry(Request $request, BalanceCalculations $balanceCalculations)
    {
        $entry = new Entry();
        $entry->setUser($this->getUser());

        $form = $this->createForm(EntryType::class, $entry);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){

            $em = $this->getDoctrine()->getManager();
            $em->persist($entry);
            $em->flush();

            $balanceCalculations->handleBalance($this->getUser(), [
               'entry' => $entry->getValue()
            ]);

            $this->addFlash('success', 'Nueva Entrada Ingresada');
        }

        return $this->render('credit/credit_card_new.html.twig',[
           'form' => $form
        ]);
    }

    /**
     * @throws \Exception
     * @Route("/list", name="list-entry")
     */
    public function egressList()
    {
        $repo = $this->getDoctrine()->getRepository(Egress::class);

        $debts = $repo->findBy([
            'user' => $this->getUser(),
            'deletedAt' => null
        ]);

        return $this->render('::base.html.twig', [
            'entries' => $debts
        ]);
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     * @Route("/new", name="new-egress")
     */
    public function newEgress(Request $request, BalanceCalculations $balanceCalculations)
    {
        $egress = new Egress();
        $egress->setUser($this->getUser());

        $form = $this->createForm(EgressType::class, $egress);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){

            $em = $this->getDoctrine()->getManager();
            $em->persist($egress);
            $em->flush();

            $balanceCalculations->handleBalance($this->getUser(),[
                'egress' => $this->getUser()
            ]);

            $this->addFlash('success', 'Nueva Entrada Ingresada');
        }

        return $this->render('credit/credit_card_new.html.twig',[
            'form' => $form
        ]);
    }


    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     * @Route("/new", name="new-egress")
     */
    public function newEgressConcept(Request $request, BalanceCalculations $balanceCalculations)
    {
        $egress = new Egress();
        $egress->setUser($this->getUser());

        $form = $this->createForm(EgressType::class, $egress);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){

            $em = $this->getDoctrine()->getManager();
            $em->persist($egress);
            $em->flush();

            $balanceCalculations->handleBalance($this->getUser(),[
                'egress' => $this->getUser()
            ]);

            $this->addFlash('success', 'Nueva Entrada Ingresada');
        }

        return $this->render('credit/credit_card_new.html.twig',[
            'form' => $form
        ]);
    }




}