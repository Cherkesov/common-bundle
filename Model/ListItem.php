<?php
namespace GFB\CommonBundle\Model;

use Doctrine\ORM\Mapping as ORM;

trait ListItem
{
    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    private $listVisible;

    /**
     * @var integer
     * @ORM\Column(type="integer")
     */
    private $listSort;

    /**
     * @return boolean
     */
    public function isListVisible()
    {
        return $this->listVisible;
    }

    /**
     * @param boolean $listVisible
     * @return ListItem
     */
    public function setListVisible($listVisible)
    {
        $this->listVisible = $listVisible;

        return $this;
    }

    /**
     * @return int
     */
    public function getListSort()
    {
        return $this->listSort;
    }

    /**
     * @param int $listSort
     * @return ListItem
     */
    public function setListSort($listSort)
    {
        $this->listSort = $listSort;

        return $this;
    }
}