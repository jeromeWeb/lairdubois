<?php

namespace Ladb\CoreBundle\Entity\Howto;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ladb\CoreBundle\Entity\AbstractDraftableAuthoredPublication;
use Symfony\Component\Validator\Constraints as Assert;
use Ladb\CoreBundle\Validator\Constraints as LadbAssert;
use Ladb\CoreBundle\Model\BodiedTrait;
use Ladb\CoreBundle\Model\CommentableTrait;
use Ladb\CoreBundle\Model\EmbeddableTrait;
use Ladb\CoreBundle\Model\IndexableTrait;
use Ladb\CoreBundle\Model\LicensedTrait;
use Ladb\CoreBundle\Model\LikableTrait;
use Ladb\CoreBundle\Model\PicturedTrait;
use Ladb\CoreBundle\Model\ScrapableTrait;
use Ladb\CoreBundle\Model\SitemapableInterface;
use Ladb\CoreBundle\Model\SitemapableTrait;
use Ladb\CoreBundle\Model\TaggableTrait;
use Ladb\CoreBundle\Model\TitledTrait;
use Ladb\CoreBundle\Model\ViewableTrait;
use Ladb\CoreBundle\Model\WatchableTrait;
use Ladb\CoreBundle\Model\IndexableInterface;
use Ladb\CoreBundle\Model\TitledInterface;
use Ladb\CoreBundle\Model\EmbeddableInterface;
use Ladb\CoreBundle\Model\PicturedInterface;
use Ladb\CoreBundle\Model\BodiedInterface;
use Ladb\CoreBundle\Model\LicensedInterface;
use Ladb\CoreBundle\Model\ViewableInterface;
use Ladb\CoreBundle\Model\LikableInterface;
use Ladb\CoreBundle\Model\WatchableInterface;
use Ladb\CoreBundle\Model\CommentableInterface;
use Ladb\CoreBundle\Model\ReportableInterface;
use Ladb\CoreBundle\Model\ExplorableInterface;
use Ladb\CoreBundle\Model\TaggableInterface;
use Ladb\CoreBundle\Model\ScrapableInterface;
use Ladb\CoreBundle\Entity\AbstractAuthoredPublication;

/**
 * @ORM\Table("tbl_howto")
 * @ORM\Entity(repositoryClass="Ladb\CoreBundle\Repository\Howto\HowtoRepository")
 */
class Howto extends AbstractDraftableAuthoredPublication implements TitledInterface, PicturedInterface, BodiedInterface, LicensedInterface, IndexableInterface, SitemapableInterface, TaggableInterface, ViewableInterface, ScrapableInterface, LikableInterface, WatchableInterface, CommentableInterface, ReportableInterface, ExplorableInterface, EmbeddableInterface
{

    use TitledTrait, PicturedTrait, BodiedTrait, LicensedTrait;
    use IndexableTrait, SitemapableTrait, TaggableTrait, ViewableTrait, ScrapableTrait, LikableTrait, WatchableTrait, CommentableTrait, EmbeddableTrait;

    const CLASS_NAME = 'LadbCoreBundle:Howto\Howto';
    const TYPE = 106;

    const KIND_NONE = 0;
    const KIND_TUTORIAL = 1;
    const KIND_TECHNICAL = 2;
    const KIND_DOCUMENTATION = 3;

    /**
     * @ORM\Column(type="string", length=100)
     * @Assert\NotBlank()
     * @Assert\Length(min=4)
     * @Assert\Regex("/^[ a-zA-Z0-9ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ'’ʼ#,.:%?!-]+$/", message="default.title.regex")
     * @ladbAssert\UpperCaseRatio()
     */
    private $title;

    /**
     * @Gedmo\Slug(fields={"title"}, separator="-")
     * @ORM\Column(type="string", length=100, unique=true)
     */
    private $slug;

    /**
     * @ORM\Column(type="smallint")
     */
    private $kind = Howto::KIND_NONE;

    /**
     * @ORM\Column(type="text", nullable=false)
     * @Assert\NotBlank()
     * @Assert\Length(min=2, max=2000)
     */
    private $body;

    /**
     * @ORM\Column(type="text", nullable=false)
     */
    private $htmlBody;

    /**
     * @ORM\Column(type="integer", name="body_block_video_count")
     */
    private $bodyBlockVideoCount = 0;

    /**
     * @ORM\ManyToOne(targetEntity="Ladb\CoreBundle\Entity\Core\Picture", cascade={"persist"})
     * @ORM\JoinColumn(name="main_picture_id", nullable=false)
     * @Assert\Type(type="Ladb\CoreBundle\Entity\Core\Picture")
     * @Assert\NotBlank()
     */
    private $mainPicture;

    /**
     * @ORM\Column(type="boolean", name="is_work_in_progress")
     */
    private $isWorkInProgress = false;

    /**
     * @ORM\Column(name="draft_article_count", type="integer")
     */
    private $draftArticleCount = 0;

    /**
     * @ORM\Column(name="published_article_count", type="integer")
     */
    private $publishedArticleCount = 0;

    /**
     * @ORM\OneToMany(targetEntity="Ladb\CoreBundle\Entity\Howto\Article", mappedBy="howto", cascade={"all"})
     * @ORM\OrderBy({"sortIndex" = "ASC"})
     */
    private $articles;

    /**
     * @ORM\Column(type="integer", name="creation_count")
     */
    private $creationCount = 0;

    /**
     * @ORM\ManyToMany(targetEntity="Ladb\CoreBundle\Entity\Wonder\Creation", mappedBy="howtos")
     */
    private $creations;

    /**
     * @ORM\Column(type="integer", name="workshop_count")
     */
    private $workshopCount = 0;

    /**
     * @ORM\ManyToMany(targetEntity="Ladb\CoreBundle\Entity\Wonder\Workshop", mappedBy="howtos")
     */
    private $workshops;

    /**
     * @ORM\Column(type="integer", name="plan_count")
     */
    private $planCount = 0;

    /**
     * @ORM\ManyToMany(targetEntity="Ladb\CoreBundle\Entity\Wonder\Plan", inversedBy="howtos", cascade={"persist"})
     * @ORM\JoinTable(name="tbl_wonder_howto_plan")
     * @Assert\Count(min=0, max=4)
     */
    private $plans;

    /**
     * @ORM\Column(type="integer", name="workflow_count")
     */
    private $workflowCount = 0;

    /**
     * @ORM\ManyToMany(targetEntity="Ladb\CoreBundle\Entity\Workflow\Workflow", inversedBy="howtos", cascade={"persist"})
     * @ORM\JoinTable(name="tbl_howto_workflow")
     * @Assert\Count(min=0, max=4)
     */
    private $workflows;

    /**
     * @ORM\Column(type="integer", name="provider_count")
     */
    private $providerCount = 0;

    /**
     * @ORM\ManyToMany(targetEntity="Ladb\CoreBundle\Entity\Knowledge\Provider", inversedBy="howtos", cascade={"persist"})
     * @ORM\JoinTable(name="tbl_howto_provider")
     * @Assert\Count(min=0, max=10)
     */
    private $providers;

    /**
     * @ORM\ManyToMany(targetEntity="Ladb\CoreBundle\Entity\Core\Tag", cascade={"persist"})
     * @ORM\JoinTable(name="tbl_howto_tag")
     * @Assert\Count(min=2)
     */
    private $tags;

    /**
     * @ORM\OneToOne(targetEntity="Ladb\CoreBundle\Entity\Core\License", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true, name="license_id")
     * @Assert\Type(type="Ladb\CoreBundle\Entity\Core\License")
     */
    private $license;

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

    /**
     * @ORM\OneToOne(targetEntity="Ladb\CoreBundle\Entity\Core\Spotlight", cascade={"remove"})
     * @ORM\JoinColumn(name="spotlight_id", referencedColumnName="id")
     */
    private $spotlight = null;

    /**
     * @ORM\ManyToOne(targetEntity="Ladb\CoreBundle\Entity\Core\Picture", cascade={"persist"})
     * @ORM\JoinColumn(name="sticker_id", nullable=true)
     */
    private $sticker;

    /**
     * @ORM\Column(type="integer", name="referral_count")
     */
    private $referralCount = 0;

    /**
     * @ORM\ManyToMany(targetEntity="Ladb\CoreBundle\Entity\Core\Referer\Referral", cascade={"persist", "remove"})
     * @ORM\JoinTable(name="tbl_wonder_howto_referral", inverseJoinColumns={@ORM\JoinColumn(name="referral_id", referencedColumnName="id", unique=true)})
     * @ORM\OrderBy({"accessCount" = "DESC"})
     */
    protected $referrals;

    /////

    public function __construct()
    {
        $this->articles = new \Doctrine\Common\Collections\ArrayCollection();
        $this->tags = new \Doctrine\Common\Collections\ArrayCollection();
        $this->creations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->workshops = new \Doctrine\Common\Collections\ArrayCollection();
        $this->plans = new \Doctrine\Common\Collections\ArrayCollection();
        $this->workflows = new \Doctrine\Common\Collections\ArrayCollection();
        $this->providers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->referrals = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /////

    // NotificationStrategy /////

    public function getNotificationStrategy()
    {
        return self::NOTIFICATION_STRATEGY_FOLLOWER;
    }

    // SubPublications /////

    public function getSubPublications()
    {
        return $this->getArticles();
    }

    // Type /////

    public function getType()
    {
        return Howto::TYPE;
    }

    // Slug /////

    public function setSlug($slug)
    {
        $this->slug = $slug;
        return $this;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function getSluggedId()
    {
        return $this->id . '-' . $this->slug;
    }

    // Kind /////

    public function getKind()
    {
        return $this->kind;
    }

    public function setKind($kind)
    {
        $this->kind = $kind;
        return $this;
    }

    // BodyBlockVideoCount /////

    public function setBodyBlockVideoCount($bodyBlockVideoCount)
    {
        $this->bodyBlockVideoCount = $bodyBlockVideoCount;
        return $this;
    }

    public function getBodyBlockVideoCount()
    {
        return $this->bodyBlockVideoCount;
    }

    // WorkInProgress /////

    public function setIsWorkInProgress($isWorkInProgress)
    {
        $this->isWorkInProgress = $isWorkInProgress;
        return $this;
    }

    public function getIsWorkInProgress()
    {
        return $this->isWorkInProgress;
    }

    // DraftArticleCount /////

    public function incrementDraftArticleCount($by = 1)
    {
        return $this->draftArticleCount += intval($by);
    }

    public function getDraftArticleCount()
    {
        return $this->draftArticleCount;
    }

    // PublishedArticleCount /////

    public function incrementPublishedArticleCount($by = 1)
    {
        return $this->publishedArticleCount += intval($by);
    }

    public function getPublishedArticleCount()
    {
        return $this->publishedArticleCount;
    }

    // Articles /////

    public function addArticle(\Ladb\CoreBundle\Entity\Howto\Article $article)
    {
        if (!$this->articles->contains($article)) {
            $this->articles[] = $article;
            $article->setHowto($this);
        }
        return $this;
    }

    public function removeArticle(\Ladb\CoreBundle\Entity\Howto\Article $article)
    {
        if ($this->articles->removeElement($article)) {
            $article->setHowto(null);
        }
    }

    public function getArticles()
    {
        return $this->articles;
    }

    public function resetArticles()
    {
        $this->articles = new \Doctrine\Common\Collections\ArrayCollection();
    }

    // CreationCount /////

    public function incrementCreationCount($by = 1)
    {
        return $this->creationCount += intval($by);
    }

    public function setCreationCount($creationCount)
    {
        $this->creationCount = $creationCount;
        return $this;
    }

    public function getCreationCount()
    {
        return $this->creationCount;
    }

    // Creations /////

    public function getCreations()
    {
        return $this->creations;
    }

    // WorkshopCount /////

    public function incrementWorkshopCount($by = 1)
    {
        return $this->workshopCount += intval($by);
    }

    public function setWorkshopCount($workshopCount)
    {
        $this->workshopCount = $workshopCount;
        return $this;
    }

    public function getWorkshopCount()
    {
        return $this->workshopCount;
    }

    // Workshops /////

    public function getWorkshops()
    {
        return $this->workshops;
    }

    // PlanCount /////

    public function incrementPlanCount($by = 1)
    {
        return $this->planCount += intval($by);
    }

    public function getPlanCount()
    {
        return $this->planCount;
    }

    // Plans /////

    public function addPlan(\Ladb\CoreBundle\Entity\Wonder\Plan $plan)
    {
        if (!$this->plans->contains($plan)) {
            $this->plans[] = $plan;
            $this->planCount = count($this->plans);
            if (!$this->getIsDraft()) {
                $plan->incrementHowtoCount();
            }
        }
        return $this;
    }

    public function removePlan(\Ladb\CoreBundle\Entity\Wonder\Plan $plan)
    {
        if ($this->plans->removeElement($plan)) {
            $this->planCount = count($this->plans);
            if (!$this->getIsDraft()) {
                $plan->incrementHowtoCount(-1);
            }
        }
    }

    public function getPlans()
    {
        return $this->plans;
    }

    // WorkflowCount /////

    public function incrementWorkflowCount($by = 1)
    {
        return $this->workflowCount += intval($by);
    }

    public function getWorkflowCount()
    {
        return $this->workflowCount;
    }

    // Workflows /////

    public function addWorkflow(\Ladb\CoreBundle\Entity\Workflow\Workflow $workflow)
    {
        if (!$this->workflows->contains($workflow)) {
            $this->workflows[] = $workflow;
            $this->workflowCount = count($this->workflows);
            if (!$this->getIsDraft()) {
                $workflow->incrementHowtoCount();
            }
        }
        return $this;
    }

    public function removeWorkflow(\Ladb\CoreBundle\Entity\Workflow\Workflow $workflow)
    {
        if ($this->workflows->removeElement($workflow)) {
            $this->workflowCount = count($this->workflows);
            if (!$this->getIsDraft()) {
                $workflow->incrementHowtoCount(-1);
            }
        }
    }

    public function getWorkflows()
    {
        return $this->workflows;
    }

    // ProviderCount /////

    public function incrementProviderCount($by = 1)
    {
        return $this->providerCount += intval($by);
    }

    public function getProviderCount()
    {
        return $this->providerCount;
    }

    // Providers /////

    public function addProvider(\Ladb\CoreBundle\Entity\Knowledge\Provider $provider)
    {
        if (!$this->providers->contains($provider)) {
            $this->providers[] = $provider;
            $this->providerCount = count($this->providers);
            if (!$this->getIsDraft()) {
                $provider->incrementHowtoCount();
            }
        }
        return $this;
    }

    public function removeProvider(\Ladb\CoreBundle\Entity\Knowledge\Provider $provider)
    {
        if ($this->providers->removeElement($provider)) {
            $this->providerCount = count($this->providers);
            if (!$this->getIsDraft()) {
                $provider->incrementHowtoCount(-1);
            }
        }
    }

    public function getProviders()
    {
        return $this->providers;
    }

    // Spotlight /////

    public function setSpotlight(\Ladb\CoreBundle\Entity\Core\Spotlight $spotlight = null)
    {
        $this->spotlight = $spotlight;
        return $this;
    }

    public function getSpotlight()
    {
        return $this->spotlight;
    }
}
