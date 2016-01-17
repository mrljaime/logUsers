<?php

namespace PicboardBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ThePicture
 *
 * @ORM\Table(name="the_picture")
 * @ORM\Entity(repositoryClass="PicboardBundle\Repository\ThePictureRepository")
 */
class ThePicture
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
     * @ORM\Column(name="path", type="string", length=255)
     */
    private $path;

    /**
     * @var string
     *
     * @ORM\Column(name="md5", type="string", length=255)
     */
    private $md5;

    /**
     * @var string
     *
     * @ORM\Column(name="section", type="string", length=255)
     */
    private $section;


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
     * Set path
     *
     * @param string $path
     *
     * @return ThePicture
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set md5
     *
     * @param string $md5
     *
     * @return ThePicture
     */
    public function setMd5($md5)
    {
        $this->md5 = $md5;

        return $this;
    }

    /**
     * Get md5
     *
     * @return string
     */
    public function getMd5()
    {
        return $this->md5;
    }

    /**
     * Set section
     *
     * @param string $section
     *
     * @return ThePicture
     */
    public function setSection($section)
    {
        $this->section = $section;

        return $this;
    }

    /**
     * Get section
     *
     * @return string
     */
    public function getSection()
    {
        return $this->section;
    }
}

