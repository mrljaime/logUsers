<?php

namespace IndexBundle\Controller;

use IndexBundle\Entity\Picture;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PictureController
 * @package IndexBundle\Controller
 * @Route("/admin/picture")
 */

class PictureController extends Controller
{
    /**
     * @Route("/upload", name="upload.picture")
     */
    public function uploadAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $file = $request->files->get('file');
        if(!is_null($file)){

            $md5 = md5_file($file);
            $result = $em->createQuery("select p from IndexBundle:Picture p where p.md5 = :md5")
                ->setParameter("md5", $md5)
                ->getOneOrNullResult();

            if(is_null($result)){

                $ext = strtolower($file->getClientOriginalExtension());
                $filename = $md5 . "." . $ext;

                $file->move(__DIR__."/../../../web/pictures/", $filename);

                $picture = new Picture();
                $picture->setMd5($md5);
                $picture->setPath($filename);
                $em->persist($picture);
                $em->flush();

                $url = $request->getUriForPath("/pictures/".$picture->getPath());

                $data = array(
                    "status" => "success",
                    "url" => $url,
                    "id" => $picture->getId(),
                );

                return new JsonResponse($data);
            }else{

                $url = $request->getUriForPath("/pictures/".$result->getPath());

                $data = array(
                    "status" => "onDb",
                    "url" => $url,
                    "id" => $result->getId(),
                );

                return new JsonResponse($data);
            }
        }
    }

    /**
     * @Route("/ver", name="ver")
     */
    public function renderAction()
    {
        return $this->render("IndexBundle::ver.html.twig");
    }
}