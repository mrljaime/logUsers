<?php

namespace IndexBundle\Controller;

use IndexBundle\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

/**
 * Class CategoryController
 * @package IndexBundle\Controller
 * @Route("admin/category")
 */
class CategoryController extends Controller
{
    /**
     * @Route("/", name="category")
     */
    public function categoryAction()
    {
        $em = $this->getDoctrine()->getManager();
        $categorys = $em->createQuery("select c from IndexBundle:Category c")
            ->getResult();

        return $this->render("IndexBundle:admin:category.html.twig", array(
            "category" => $categorys,
            "active" => "categorias",
        ));
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/create", name="create.category")
     */
    public function createCategory(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $category = new Category();

        $form = $this->createFormBuilder($category)
            ->add('name', TextType::class, array("label" => "Nombre"))
            ->add('isActive', CheckBoxType::class, array("label" => "Activo", "required" => false))
            ->add("save", SubmitType::class, array("label" => "Crear"))
            ->getForm();


        if($request->getMethod() == "POST"){
            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){
                $data = $form->getData();

                $category->setName($data->getName());
                $category->setIsActive($this->handActiveCheck($data->getIsActive()));
                $category->setCreatedAt(new \DateTime());
                $em->persist($category);
                $em->flush();

                $this->addFlash("msg", "La categoria se creo correctamente");
                return $this->redirectToRoute("category");
            }
        }

        return $this->render("IndexBundle:admin:newCategory.html.twig", array(
            'form' => $form->createView(),
            'active' => "categorias"
        ));
    }


    /**
     * @Route("/delete/{id}", name="delete.category")
     */
    public function deleteCategory(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $result = $em->createQuery("select c from IndexBundle:Category c where c.id = :id")
            ->setParameter("id", $id)
            ->getOneOrNullResult();

        if(!is_null($result)){
            $em->remove($result);
            $em->flush();

            $this->addFlash("msg", "La categoria se ha eliminado correctamente");
            return $this->redirectToRoute("category");
        }else{
            $this->addFlash("alert", "No se ha podido eliminar el elemento");
            return $this->redirectToRoute("category");
        }
    }

    /**
     * @Route("/edit/{id}", name="edit.category")
     */
    public function editCategory(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $category = new Category();

        $result = $em->createQuery("select c from IndexBundle:Category c where c.id = :id")
            ->setParameter("id", $id)
            ->getOneOrNullResult();

        $form = $this->createFormBuilder($category)
            ->add("name", TextType::class, array("label" => "Nombre", "attr" => array(
                "value" => $result->getName(),
            )))
            ->add("isActive", CheckboxType::class, array("label" => "Activa", "required" => false,
                "attr" => array(
                    "checked" => $category->getIsActive(),
                    "value" => 1,
                )))
            ->add("save", SubmitType::class, array("label" => "Guardar cambio"))
            ->getForm();

        if($request->getMethod() != "POST") {
            if ($result) {

                return $this->render("IndexBundle:admin:editCategory.html.twig", array(
                    "form" => $form->createView(),
                    "active" => "categorias"
                ));
            } else {
                return $this->redirect($request->server->get("HTTP_REFERER"));
            }
        }else{
            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){
                $data = $form->getData();

                $result->setName($data->getName());
                $result->setIsActive($this->handActiveCheck($data->getIsActive()));
                $em->persist($result);
                $em->flush();

                $this->addFlash("msg", "La categoria se ha editado correctamente");
                return $this->redirectToRoute("category");
            }else{
                $this->addFlash("alert", "Algo salio mal");
                return $this->redirectToRoute("category");
            }
        }
    }

    /**
     * Maneja los checkbox de los activos o visibles
     * @param $input
     * @return bool
     */
    private function handActiveCheck($input)
    {
        if(is_null($input) || empty($input)){
            return false;
        }else{
            return true;
        }
    }
}
