<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 6/04/2019
 * Time: 9:53 AM
 */

namespace App\Controller\Sfa;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 *
 * @Route("menu")
 * */
class MenuController extends AbstractController
{
    /**
     * @Route("view", name="view_menu")
     * */
    public function menu()
    {
       return $this->renderView('includes/menu.html.twig', [
           'menu' => 'Mi Menu'
       ]);
    }
}