<?php

namespace Ladb\CoreBundle\Entity\Faq;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Ladb\CoreBundle\Validator\Constraints as LadbAssert;
use Ladb\CoreBundle\Entity\AbstractDraftableAuthoredPublication;
use Ladb\CoreBundle\Model\BlockBodiedTrait;
use Ladb\CoreBundle\Model\CommentableTrait;
use Ladb\CoreBundle\Model\IndexableTrait;
use Ladb\CoreBundle\Model\LikableTrait;
use Ladb\CoreBundle\Model\ScrapableTrait;
use Ladb\CoreBundle\Model\SitemapableInterface;
use Ladb\CoreBundle\Model\SitemapableTrait;
use Ladb\CoreBundle\Model\TaggableTrait;
use Ladb\CoreBundle\Model\TitledTrait;
use Ladb\CoreBundle\Model\ViewableTrait;
use Ladb\CoreBundle\Model\WatchableTrait;
use Ladb\CoreBundle\Model\ScrapableInterface;
use Ladb\CoreBundle\Model\ExplorableInterface;
use Ladb\CoreBundle\Model\IndexableInterface;
use Ladb\CoreBundle\Model\TitledInterface;
use Ladb\CoreBundle\Model\BlockBodiedInterface;
use Ladb\CoreBundle\Model\ViewableInterface;
use Ladb\CoreBundle\Model\LikableInterface;
use Ladb\CoreBundle\Model\WatchableInterface;
use Ladb\CoreBundle\Model\CommentableInterface;
use Ladb\CoreBundle\Model\ReportableInterface;
use Ladb\CoreBundle\Model\TaggableInterface;

/**
 * @ORM\Table("tbl_faq_question")
 * @ORM\Entity(repositoryClass="Ladb\CoreBundle\Repository\Faq\QuestionRepository")
 * @LadbAssert\BodyBlocks()
 */
class Question extends AbstractDraftableAuthoredPublication implements TitledInterface, BlockBodiedInterface, IndexableInterface, SitemapableInterface, TaggableInterface, ViewableInterface, ScrapableInterface, LikableInterface, WatchableInterface, CommentableInterface, ReportableInterface, ExplorableInterface
{

    use TitledTrait, BlockBodiedTrait;
    use IndexableTrait, SitemapableTrait, TaggableTrait, ViewableTrait, ScrapableTrait, LikableTrait, WatchableTrait, CommentableTrait;

    const CLASS_NAME = 'LadbCoreBundle:Faq\Question';
    const TYPE = 110;

    /**
     * @ORM\Column(name="weight", type="integer")
     */
    private $weight = 0;

    /**
     * @ORM\Column(type="string", length=100)
     * @Assert\NotBlank()
     * @Assert\Length(min=4)
     */
    private $title;

    /**
     * @Gedmo\Slug(fields={"title"}, separator="-")
     * @ORM\Column(type="string", length=100, unique=true)
     */
    private $slug;

    /**
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank()
     */
    private $icon;

    /**
     * @ORM\Column(type="text", nullable=false)
     */
    private $body;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $bodyExtract;

    /**
     * @ORM\ManyToMany(targetEntity="Ladb\CoreBundle\Entity\Core\Block\AbstractBlock", cascade={"persist", "remove"})
     * @ORM\JoinTable(name="tbl_faq_question_body_block", inverseJoinColumns={@ORM\JoinColumn(name="block_id", referencedColumnName="id", unique=true, onDelete="cascade")})
     * @ORM\OrderBy({"sortIndex" = "ASC"})
     * @Assert\Count(min=1)
     */
    private $bodyBlocks;

    /**
     * @ORM\Column(type="integer", name="body_block_picture_count")
     */
    private $bodyBlockPictureCount = 0;

    /**
     * @ORM\Column(type="integer", name="body_block_video_count")
     */
    private $bodyBlockVideoCount = 0;

    /**
     * @ORM\Column(type="boolean", name="has_toc")
     */
    private $hasToc = false;

    /**
     * @ORM\ManyToMany(targetEntity="Ladb\CoreBundle\Entity\Core\Tag", cascade={"persist"})
     * @ORM\JoinTable(name="tbl_faq_question_tag")
     * @Assert\Count(min=1)
     */
    private $tags;

    /**
     * @ORM\Column(type="integer", name="like_count")
     */
    private $likeCount = 0;

    /**
     * @ORM\Column(type="integer", name="watch_count")
     */
    private $watchCount = 0;

    /**
     * @ORM\Column(type="integer", name="comment_count")
     */
    private $commentCount = 0;

    /**
     * @ORM\Column(type="integer", name="view_count")
     */
    private $viewCount = 0;

    /////

    public function __construct()
    {
        $this->bodyBlocks = new \Doctrine\Common\Collections\ArrayCollection();
        $this->tags = new \Doctrine\Common\Collections\ArrayCollection();
    }

    // Type /////

    public function getType()
    {
        return Question::TYPE;
    }

    // Weight /////

    public function getWeight()
    {
        return $this->weight;
    }

    public function setWeight($weight)
    {
        $this->weight = $weight;
        return $this;
    }

    // Slug /////

    public function getSlug()
    {
        return $this->slug;
    }

    public function setSlug($slug)
    {
        $this->slug = $slug;
        return $this;
    }

    public function getSluggedId()
    {
        return $this->id . '-' . $this->slug;
    }

    // Icon /////

    public function getIcon()
    {
        return $this->icon;
    }

    public function setIcon($icon)
    {
        $this->icon = $icon;
        return $this;
    }

    // HasToc /////

    public function getHasToc()
    {
        return $this->hasToc;
    }

    public function setHasToc($hasToc)
    {
        $this->hasToc = $hasToc;
        return $this;
    }
}
