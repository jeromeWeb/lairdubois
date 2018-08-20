<?php

namespace Ladb\CoreBundle\Entity\Core;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table("tbl_core_registration")
 * @ORM\Entity(repositoryClass="Ladb\CoreBundle\Repository\Core\RegistrationRepository")
 */
class Registration
{

    const CLASS_NAME = 'LadbCoreBundle:Core\Registration';

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
    protected $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="Ladb\CoreBundle\Entity\Core\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(name="client_ip4", type="string")
     */
    private $clientIp4;

    /**
     * @ORM\Column(name="client_ip6", type="string", nullable=true)
     */
    private $clientIp6;

    /**
     * @ORM\Column(name="client_user_agent", type="string", nullable=true)
     */
    private $clientUserAgent;

    /////

    // Id /////

    public function getId()
    {
        return $this->id;
    }

    // CreatedAt /////

    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    // Age /////

    public function getAge()
    {
        return $this->getCreatedAt()->diff(new \DateTime());
    }

    // User /////

    public function setUser(\Ladb\CoreBundle\Entity\Core\User $user)
    {
        $this->user = $user;
        return $this;
    }

    public function getUser()
    {
        return $this->user;
    }

    // ClientIp4 /////

    public function setClientIp4($clientIp4)
    {
        $this->clientIp4 = $clientIp4;
        return $this;
    }

    public function getClientIp4()
    {
        return $this->clientIp4;
    }

    // ClientIp6 /////

    public function setClientIp6($clientIp6)
    {
        $this->clientIp6 = $clientIp6;
        return $this;
    }

    public function getClientIp6()
    {
        return $this->clientIp6;
    }

    // ClientUserAgent /////

    public function setClientUserAgent($clientUserAgent)
    {
        $this->clientUserAgent = $clientUserAgent;
        return $this;
    }

    public function getClientUserAgent()
    {
        return $this->clientUserAgent;
    }
}
