<?php
/**
 * Created by PhpStorm.
 * User: jaime
 * Date: 1/2/16
 * Time: 12:24 AM
 */

namespace PicboardBundle\Controller;


use Doctrine\Common\Proxy\Exception\InvalidArgumentException;
use PicboardBundle\Entity\ThePicture;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        $em = $this->getDoctrine()->getManager();
        if($request->getMethod() == "POST"){
            $files = $request->files;
            if(is_null($files)){
                throw new InvalidArgumentException("El campo no puede estar nulo");
            }
            foreach ($files as $file) {
                $md5 = md5_file($file);
                $result = $em->createQuery("select p from PicboardBundle:ThePicture p where p.md5 = :md5")
                    ->setParameter("md5", $md5)
                    ->getOneOrNullResult();

                if (is_null($result)) {

                    $ext = strtolower($file->getClientOriginalExtension());
                    $filename = $md5 . "." . $ext;

                    $file->move(__DIR__ . "/../../../web/pictures/", $filename);

                    $picture = new ThePicture();
                    $picture->setMd5($md5);
                    $picture->setPath($filename);
                    $em->persist($picture);
                    $em->flush();

                    $url = $request->getUriForPath("/pictures/" . $picture->getPath());

                    $data = array(
                        "status" => "success",
                        "url" => $url,
                        "id" => $picture->getId(),
                    );

                    return $this->redirectToRoute("pic.upload.pictures");
                } else {

                    $url = $request->getUriForPath("/pictures/" . $result->getPath());

                    $data = array(
                        "status" => "onDb",
                        "url" => $url,
                        "id" => $result->getId(),
                    );

                    return $this->redirectToRoute("pic.upload.pictures");
                }
            }
        }

        return $this->render("PicboardBundle:picboard:uploadImage.html.twig");
    }

    /**
     * @Route("uploadFile", name="pic.upload.file")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function uploadFilesAction(Request $request)
    {
        $this->get("logger")->debug("Entramos al subidor de imágenes");
        $em = $this->getDoctrine()->getManager();
        $files = $request->files;
        $this->get("logger")->debug("Despues de sacar las imagenes del request");
        $this->get("logger")->debug(sprintf("Conteo de los archivos a subir: %s", count($files)));
        $data = array();
        if (count($files) > 0) {
            foreach ($files as $file) {
                $md5 = md5_file($file);
                $result = $em->createQuery("select p from PicboardBundle:ThePicture p where p.md5 = :md5")
                    ->setParameter("md5", $md5)
                    ->getOneOrNullResult();

                if (is_null($result)) {
                    $ext = strtolower($file->getClientOriginalExtension());
                    $fileName = $md5 . "." . $ext;
                    $file->move('pictures/', $fileName);
                    $this->get("logger")->debug(sprintf("El nombre del archivo es: %s", $fileName));
                    $picture = new ThePicture();
                    $picture->setMd5($md5);
                    $picture->setPath($fileName);
                    $em->persist($picture);
                    $em->flush();

                    $url = $request->getUriForPath("/pictures/" . $picture->getPath());
                    $content = array(
                        'url' => $url,
                    );
                    array_push($data, $content);
                } else {
                    $url = $request->getUriForPath("/pictures/" . $result->getPath());
                    $content = array(
                        'url' => $url,
                    );
                    array_push($data, $content);
                }
            }
            return new JsonResponse($data);
        }
        throw new InvalidArgumentException("No se pueden subir imagenes si no existen");
    }


}