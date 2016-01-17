<?php
/**
 * Created by PhpStorm.
 * User: jaime
 * Date: 1/2/16
 * Time: 12:24 AM
 */

namespace PicboardBundle\Controller;


use PicboardBundle\Entity\ThePicture;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;


/**
 * Class PictureController
 * @package PicboardBundle\Controller
 * @Route("picboard/")
 */
class PictureController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("index", name="theIndex")
     */
    public function indexAction()
    {
        return $this->render("PicboardBundle:picboard:upload.html.twig");
    }

    /**
     * @param Request $request
     * @Route("upload", name="pic.upload.picture")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function uploadAction(Request $request)
    {
        return $this->render("PicboardBundle:picboard:uploadImage.html.twig");
    }
}