<?php

namespace IndexBundle\Controller;

use IndexBundle\Entity\Category;
use IndexBundle\Entity\SubCat;
use IndexBundle\Repository\CategoryRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

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

        $post = $em
            ->createQuery("select count(p) from IndexBundle:Post p where p.categoryId = :id")
            ->setParameter("id", $result->getId())
            ->getSingleScalarResult();

        if($post >= 1){
            $this->addFlash("alert", "La categoría tiene dependencias! No puedes eliminarla");
            return $this->redirect($request->server->get("HTTP_REFERER"));
        }

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
     * @Route("/subCategory", name="category_sub_category")
     */
    public function indexSubCatAction()
    {
        $em = $this->getDoctrine()->getManager();
        $subCat = $em->createQuery("select sc from IndexBundle:SubCat sc")
            ->getResult();

        return $this->render("IndexBundle:admin:subCat.html.twig", array(
            'subCategories' => $subCat,
            'active' => "subcat",
        ));
    }

    /**
     * @Route("/subCategory/create", name="category_sub_category_create")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function createSubCategoryAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $subCategory = new SubCat();
        $categories = $em->createQuery("select c from IndexBundle:Category c where c.isActive = true")
            ->getResult();
        if(count($categories) == 0) {
            $this->addFlash("alert", "No existe ninguna categoría o no hay ninguna activa");
            return $this->redirectToRoute("category");
        }

        $form = $this->createFormBuilder($subCategory)
            ->add("name", TextType::class, array('label' => "Nombre"))
            ->add("categoryId", EntityType::class,
                array(
                    'label' => 'Categorías',
                    'class' => "IndexBundle:Category",
                    'choice_label' => "name",
                    'query_builder' => function(CategoryRepository $cr) {
                        return $cr->createQueryBuilder("cr")
                            ->where("cr.isActive = true");
                    }
                    ))
            ->add("save", SubmitType::class, array('label' => "Guardar sub categoría"))
            ->getForm();

        if ($request->getMethod() == "POST") {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();
                $category = $this->getRepository($request, "Category", $data->getCategoryId());
                $subCategory->setName($data->getName());
                $subCategory->setCategoryId($category);
                $em->persist($subCategory);
                $em->flush();
                $this->addFlash("msg", "Sub categoría creada correctamente");
                return $this->redirectToRoute("category_sub_category");
            } else {
                return $this->render("IndexBundle:admin:subCatCreate.html.twig", array(
                    'form' => $form->createView(),
                    'active' => 'subcat',
                ));
            }
        }
        return $this->render("IndexBundle:admin:subCatCreate.html.twig", array(
            'form' => $form->createView(),
            'active' => 'subcat',
        ));
    }

    /**
     * @Route("/subCategory/{id}/delete", name="category_sub_delete")
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteSubCatAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $subCat = $em->getRepository("IndexBundle:SubCat")->find($id);
        $posts = $em->createQuery("select count(p) from IndexBundle:Post p where p.categoryId = :id")
            ->setParameter("id", $subCat)
            ->getScalarResult();
        if (count($posts) > 0) {
            $this->addFlash("alert", "No puedes eliminar una sub categoria que tiene publicaciones");
            return $this->redirectToRoute("category_sub_category");
        }


        if ($subCat) {
            $em->remove($subCat);
            $em->flush();
            $this->addFlash("msg", "Sub categoría eliminada correctamente");
            return $this->redirectToRoute("category_sub_category");
        }
        $this->addFlash("alert", sprintf("No se encontró categoría asociada a id %s", $id));
        return $this->redirectToRoute("category_sub_category");
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

    private function getRepository($request, $name, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $element = $em->getRepository("IndexBundle:$name")->find($id);
        if (is_null($element)) {
            $this->addFlash("alert", "No se encontró elemento $name");
            return $this->redirect($request->server->get("HTTP_REFERER"));
        }
        return $element;
    }
}
