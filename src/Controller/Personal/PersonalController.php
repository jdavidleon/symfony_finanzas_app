<?php
/**
 * Created by PhpStorm.
 * User: IT OPTIME
 * Date: 24/01/2019
 * Time: 11:03 AM
 */

namespace App\Controller\Personal;


use App\Entity\Personal\Entry;
use App\Form\Personal\EntryType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;


/**
 *
 * @Route("/entries")
 *
 * */
class PersonalController extends Controller
{

    /**
     * @throws \Exception
     * @Route("/list", name="list-entry")
     */
    public function listEntry()
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
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     * @Route("/new", name="new-entry")
     */
    public function newEntry(Request $request)
    {
        $entry = new Entry();
        $entry->setUser($this->getUser());

        $form = $this->createForm(EntryType::class, $entry);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){

            $em = $this->getDoctrine()->getManager();
            $em->persist($entry);
            $em->flush();

            $this->addFlash('success', 'Nueva Entrada Ingresada');
        }

        return $this->render('credit/credit_card_new.html.twig',[
           'form' => $form
        ]);
    }
}