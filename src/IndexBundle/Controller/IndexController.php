<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 15/12/2015
 * Time: 05:50 PM
 */

namespace IndexBundle\Controller;

use IndexBundle\Entity\Users;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Security;

class IndexController extends Controller
{
    /**
     * @Route("/admin/login", name="login_route")
     */
    public function loginAction(Request $request)
    {

        $authenticationUtils = $this->get('security.authentication_utils');

        $error = $authenticationUtils->getLastAuthenticationError();

        $data = array(
            'error' => $error,
        );

        return $this->render("IndexBundle:admin:login.html.twig", $data);
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