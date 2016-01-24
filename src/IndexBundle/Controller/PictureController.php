<?php

namespace IndexBundle\Controller;

use Doctrine\Common\Proxy\Exception\InvalidArgumentException;
use IndexBundle\Entity\Picture;
use IndexBundle\Entity\Post;
use IndexBundle\Entity\Slider;
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

    /**
     * @Route("/slider/create", name="picture_slider_create")
     * @param Request $request
     * @return JsonResponse
     */
    public function sliderCreateAction(Request $request)
    {
    	$em = $this->getDoctrine()->getManager();
        $postId = $request->get("postId");
    	$files = $request->files;
        $picture = new Picture();
        $result = array();
        $post = $em->createQuery("select p from IndexBundle:Post p where p.id=:id")
            ->setParameter("id", $postId)
            ->getOneOrNullResult();
        if (is_null($post)) {
            throw new InvalidArgumentException("No existe publicacion asociada a id");
        }

        foreach ($files as $file) {
            $md5 = md5_file($file);
            $select = $em->createQuery("select p from IndexBundle:Picture p where p.md5=:md5")
                ->setParameter("md5", $md5)
                ->getOneOrNullResult();
            if (is_null($select)) {
                $ext = strtolower($file->getClientOriginalExtension());
                $fileName = $md5 . "." . $ext;
                $file->move(__DIR__."/../../../web/pictures/", $fileName);
                $picture->setMd5($md5);
                $picture->setPath($fileName);
                $picture->setSection("slider");
                $em->persist($picture);
                $em->flush();

                $this->persistAndFlushSlider($picture, $post);
                $url = $request->getUriForPath("/pictures/".$fileName);
                $data = array(
                    'url' => $url,
                );
                array_push($result, $data);
            } else {
                $url = $request->getUriForPath("/pictures/".$select->getPath());
                $this->persistAndFlushSlider($select, $post);
                $data = array(
                    'url' => $url,
                );
                array_push($result, $data);
            }
        }

        return new JsonResponse($result);
    }

    private function persistAndFlushSlider(Picture $picture, Post $post)
    {
        $em = $this->getDoctrine()->getManager();
        $slider = new Slider();
        $slider->setPostId($post);
        $slider->setPictureId($picture);
        $slider->setCreatedAt(new \DateTime());
        $em->persist($slider);
        $em->flush();
    }
}