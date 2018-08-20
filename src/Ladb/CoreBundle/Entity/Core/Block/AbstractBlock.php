<?php

namespace Ladb\CoreBundle\Entity\Core\Block;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table("tbl_core_block")
 * @ORM\Entity
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="integer")
 * @ORM\DiscriminatorMap({1 = "Text", 2 = "Gallery", 3 = "Video"})
 */
abstract class AbstractBlock
{

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;

    /**
     * @ORM\Column(name="updated_at", type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    private $updatedAt;

    /**
     * @ORM\Column(name="sort_index", type="integer")
     */
    private $sortIndex = 0;

    /////

    // StrippedName /////

    abstract public function getStrippedName();

    // Id /////

    public function getId()
    {
        return $this->id;
    }

    // CreatedAt /////

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    // UpdatedAt /////

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    // sortIndex /////

    public function getSortIndex()
    {
        return $this->sortIndex;
    }

    public function setSortIndex($sortIndex)
    {
        $this->sortIndex = $sortIndex;
        return $this;
    }
}
