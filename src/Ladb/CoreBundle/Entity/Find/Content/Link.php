<?php

namespace Ladb\CoreBundle\Entity\Find\Content;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\MappedSuperclass
 */
class Link extends AbstractContent
{

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Assert\NotBlank(groups={"link"})
     * @Assert\Url(groups={"link"})
     */
    private $url;

    /**
     * @ORM\ManyToOne(targetEntity="Ladb\CoreBundle\Entity\Core\Picture", cascade={"persist"})
     * @ORM\JoinColumn(name="thumbnail_id", nullable=true)
     * @Assert\Type(type="Ladb\CoreBundle\Entity\Core\Picture")
     */
    private $thumbnail;

    // Url /////

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    // Thumbnail /////

    public function getThumbnail()
    {
        return $this->thumbnail;
    }

    public function setThumbnail(\Ladb\CoreBundle\Entity\Core\Picture $thumbnail = null)
    {
        $this->thumbnail = $thumbnail;
        return $this;
    }
}
