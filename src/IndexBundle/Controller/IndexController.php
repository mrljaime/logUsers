<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 15/12/2015
 * Time: 05:50 PM
 */

namespace IndexBundle\Controller;

use IndexBundle\Entity\Users;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class IndexController extends Controller
{
    /**
     * @Route("/admin/login", name="login_route")
     */
    public function loginAction(Request $request)
    {
        return $this->render("IndexBundle:admin:login.html.twig");
    }

    /**
     * @Route("/admin/dologin", name="dologin")
     */
    public function loginCheckAction()
    {
        // this controller will not be executed,
        // as the route is handled by the Security system
    }


    /**
     * @Route("/admin/logout", name="logout")
     */
    public function logoutAction()
    {
    }


}