<?php

namespace Ladb\CoreBundle\Entity\Message;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table("tbl_message_meta")
 * @ORM\Entity(repositoryClass="Ladb\CoreBundle\Repository\Message\MessageMetaRepository")
 */
class MessageMeta
{

    const CLASS_NAME = 'LadbCoreBundle:Message\MessageMeta';

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Ladb\CoreBundle\Entity\Message\Message", inversedBy="metas")
     */
    private $message;

    /**
     * @ORM\ManyToOne(targetEntity="Ladb\CoreBundle\Entity\Core\User")
     * @ORM\JoinColumn(name="participant_user_id", referencedColumnName="id")
     */
    private $participant;

    /**
     * @ORM\Column(type="boolean", name="is_read")
     */
    private $isRead = false;

    // Id /////

    public function getId()
    {
        return $this->id;
    }

    // Message /////

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage(\Ladb\CoreBundle\Entity\Message\Message $message)
    {
        $this->message = $message;
        return $this;
    }

    // Participant /////

    public function getParticipant()
    {
        return $this->participant;
    }

    public function setParticipant(\Ladb\CoreBundle\Entity\Core\User $participant)
    {
        $this->participant = $participant;
        return $this;
    }

    // IsRead /////

    public function getIsRead()
    {
        return $this->isRead;
    }

    public function setIsRead($isRead)
    {
        $this->isRead = $isRead;
        return $this;
    }
}
