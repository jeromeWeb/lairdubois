<?php

namespace Ladb\CoreBundle\Entity\Knowledge\Value;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Ladb\CoreBundle\Validator\Constraints as LadbAssert;

/**
 * @ORM\Table("tbl_knowledge2_value_phone")
 * @ORM\Entity(repositoryClass="Ladb\CoreBundle\Repository\Knowledge\Value\PhoneRepository")
 * @ladbAssert\ValidPhoneValue()
 */
class Phone extends BaseValue
{

    const CLASS_NAME = 'LadbCoreBundle:Knowledge\Value\Phone';
    const TYPE = 15;

    const TYPE_STRIPPED_NAME = 'phone';

    /**
     * @ORM\Column(type="string", length=20)
     */
    protected $data;

    /**
     * @ORM\Column(type="string", length=20)
     * @Assert\NotBlank
     * @Assert\Length(max=20)
     */
    protected $rawPhoneNumber;

    /**
     * @ORM\Column(type="string", length=2)
     * @Assert\NotBlank
     */
    protected $country = 'FR';

    /////

    // Type /////

    public function getType()
    {
        return self::TYPE;
    }

    // RawPhoneNumber /////

    public function setRawPhoneNumber($rawPhoneNumber)
    {
        $this->rawPhoneNumber = $rawPhoneNumber;
        return $this;
    }

    public function getRawPhoneNumber()
    {
        return $this->rawPhoneNumber;
    }

    // Country /////

    public function setCountry($country)
    {
        $this->country = $country;
        return $this;
    }

    public function getCountry()
    {
        return $this->country;
    }
}
