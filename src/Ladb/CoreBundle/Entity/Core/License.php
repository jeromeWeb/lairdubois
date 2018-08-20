<?php

namespace Ladb\CoreBundle\Entity\Core;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table("tbl_core_license")
 * @ORM\Entity
 */
class License
{

    const CLASS_NAME = 'LadbCoreBundle:Core\License';
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="boolean", nullable=true, name="allow_derivs")
     */
    private $allowDerivs = true;

    /**
     * @ORM\Column(type="boolean", nullable=true, name="share_alike")
     */
    private $shareAlike = true;

    /**
     * @ORM\Column(type="boolean", nullable=true, name="allow_commercial")
     */
    private $allowCommercial = false;

    /////

    public function __construct($allowDerivs = true, $shareAlike = true, $allowCommercial = false)
    {
        $this->allowDerivs = $allowDerivs;
        $this->shareAlike = $shareAlike;
        $this->allowCommercial = $allowCommercial;
    }

    /////

    // Id /////

    public function getId()
    {
        return $this->id;
    }

    // AllowDerivs /////

    public function getAllowDerivs()
    {
        return $this->allowDerivs;
    }

    public function setAllowDerivs($allowDerivs)
    {
        $this->allowDerivs = $allowDerivs;
        return $this;
    }

    // ShareAlike /////

    public function getShareAlike()
    {
        return $this->shareAlike;
    }

    public function setShareAlike($shareAlike)
    {
        $this->shareAlike = $shareAlike;
        return $this;
    }

    // AllowCommercial /////

    public function getAllowCommercial()
    {
        return $this->allowCommercial;
    }

    public function setAllowCommercial($allowCommercial)
    {
        $this->allowCommercial = $allowCommercial;
        return $this;
    }

    // StrippedName /////

    public function getBadgeUrl()
    {
        return '/bundles/ladbcore/ladb/images/cc/88x31/' . $this->getStrippedName() . '.png';
    }

    // BadgeUrl /////

    public function getStrippedName()
    {
        $strippedName = 'by';
        if (!$this->allowCommercial) {
            $strippedName .= '-nc';
        }
        if ($this->shareAlike) {
            $strippedName .= '-sa';
        } elseif (!$this->allowDerivs) {
            $strippedName .= '-nd';
        }
        return $strippedName;
    }

    // BadgeUrl /////

    public function getMiniBadgeUrl()
    {
        return '/bundles/ladbcore/ladb/images/cc/80x15/' . $this->getStrippedName() . '.png';
    }

    // ContentUrl /////

    public function getContentUrl($locale = 'fr')
    {
        return 'http://creativecommons.org/licenses/' . $this->getStrippedName() . '/3.0/deed.' . $locale;
    }
}
