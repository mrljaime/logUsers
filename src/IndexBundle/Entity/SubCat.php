<?php

namespace IndexBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * SubCat
 *
 * @ORM\Table(name="sub_cat")
 * @ORM\Entity(repositoryClass="IndexBundle\Repository\SubCatRepository")
 */
class SubCat
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank(message="El campo no puede estar vacio")
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\ManyToOne(targetEntity="Category")
     * @ORM\JoinColumn(name="cateogy_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $categoryId;



    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return SubCat
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set categoryId
     *
     * @param integer $categoryId
     *
     * @return SubCat
     */
    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;

        return $this;
    }

    /**
     * Get categoryId
     *
     * @return int
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }
}

