<?php

namespace Ladb\CoreBundle\Controller\Faq;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Ladb\CoreBundle\Entity\Faq\Question;
use Ladb\CoreBundle\Form\Type\Faq\QuestionType;
use Ladb\CoreBundle\Utils\TagUtils;
use Ladb\CoreBundle\Utils\CommentableUtils;
use Ladb\CoreBundle\Utils\FollowerUtils;
use Ladb\CoreBundle\Utils\LikableUtils;
use Ladb\CoreBundle\Utils\WatchableUtils;
use Ladb\CoreBundle\Utils\SearchUtils;
use Ladb\CoreBundle\Utils\FieldPreprocessorUtils;
use Ladb\CoreBundle\Utils\ExplorableUtils;
use Ladb\CoreBundle\Utils\BlockBodiedUtils;
use Ladb\CoreBundle\Event\PublicationEvent;
use Ladb\CoreBundle\Event\PublicationListener;
use Ladb\CoreBundle\Event\PublicationsEvent;
use Ladb\CoreBundle\Manager\Faq\QuestionManager;
use Ladb\CoreBundle\Manager\Core\WitnessManager;
use Ladb\CoreBundle\Model\HiddableInterface;

/**
 * @Route("/faq")
 */
class QuestionController extends Controller
{

    /**
     * @Route("/new", name="core_faq_question_new")
     * @Template("LadbCoreBundle:Faq/Question:new.html.twig")
     * @Security("has_role('ROLE_ADMIN')", statusCode=404, message="Not allowed (core_faq_question_new)")
     */
    public function newAction()
    {

        $question = new Question();
        $question->addBodyBlock(new \Ladb\CoreBundle\Entity\Core\Block\Text());     // Add a default Text body block
        $form = $this->createForm(QuestionType::class, $question);

        $tagUtils = $this->get(TagUtils::NAME);

        return array(
            'form'         => $form->createView(),
            'tagProposals' => $tagUtils->getProposals($question),
        );
    }

    /**
     * @Route("/create", name="core_faq_question_create")
     * @Method("POST")
     * @Template("LadbCoreBundle:Faq/Question:new.html.twig")
     * @Security("has_role('ROLE_ADMIN')", statusCode=404, message="Not allowed (core_faq_question_create)")
     */
    public function createAction(Request $request)
    {
        $om = $this->getDoctrine()->getManager();

        $question = new Question();
        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isValid()) {

            $blockUtils = $this->get(BlockBodiedUtils::NAME);
            $blockUtils->preprocessBlocks($question);

            $fieldPreprocessorUtils = $this->get(FieldPreprocessorUtils::NAME);
            $fieldPreprocessorUtils->preprocessFields($question);

            $question->setUser($this->getUser());

            $om->persist($question);
            $om->flush();

            // Dispatch publication event
            $dispatcher = $this->get('event_dispatcher');
            $dispatcher->dispatch(PublicationListener::PUBLICATION_CREATED, new PublicationEvent($question));

            return $this->redirect($this->generateUrl('core_faq_question_show', array( 'id' => $question->getSluggedId() )));
        }

        // Flashbag
        $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('default.form.alert.error'));

        $tagUtils = $this->get(TagUtils::NAME);

        return array(
            'question'     => $question,
            'form'         => $form->createView(),
            'tagProposals' => $tagUtils->getProposals($question),
        );
    }

    /**
     * @Route("/{id}/publish", requirements={"id" = "\d+"}, name="core_faq_question_publish")
     * @Security("has_role('ROLE_ADMIN')", statusCode=404, message="Not allowed (core_faq_question_publish)")
     */
    public function publishAction($id)
    {
        $om = $this->getDoctrine()->getManager();
        $questionRepository = $om->getRepository(Question::CLASS_NAME);

        $question = $questionRepository->findOneByIdJoinedOnUser($id);
        if (is_null($question)) {
            throw $this->createNotFoundException('Unable to find Question entity (id=' . $id . ').');
        }
        if ($question->getIsDraft() === false) {
            throw $this->createNotFoundException('Already published (core_faq_question_publish)');
        }

        // Publish
        $questionManager = $this->get(QuestionManager::NAME);
        $questionManager->publish($question);

        // Flashbag
        $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('faq.question.form.alert.publish_success', array( '%title%' => $question->getTitle() )));

        return $this->redirect($this->generateUrl('core_faq_question_show', array( 'id' => $question->getSluggedId() )));
    }

    /**
     * @Route("/{id}/unpublish", requirements={"id" = "\d+"}, name="core_faq_question_unpublish")
     * @Security("has_role('ROLE_ADMIN')", statusCode=404, message="Not allowed (core_faq_question_unpublish)")
     */
    public function unpublishAction(Request $request, $id)
    {
        $om = $this->getDoctrine()->getManager();
        $questionRepository = $om->getRepository(Question::CLASS_NAME);

        $question = $questionRepository->findOneByIdJoinedOnUser($id);
        if (is_null($question)) {
            throw $this->createNotFoundException('Unable to find Question entity (id=' . $id . ').');
        }
        if ($question->getIsDraft() === true) {
            throw $this->createNotFoundException('Already draft (core_faq_question_unpublish)');
        }

        // Unpublish
        $questionManager = $this->get(QuestionManager::NAME);
        $questionManager->unpublish($question);

        // Flashbag
        $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('faq.question.form.alert.unpublish_success', array( '%title%' => $question->getTitle() )));

        // Return to
        $returnToUrl = $request->get('rtu');
        if (is_null($returnToUrl)) {
            $returnToUrl = $request->headers->get('referer');
        }

        return $this->redirect($returnToUrl);
    }

    /**
     * @Route("/{id}/edit", requirements={"id" = "\d+"}, name="core_faq_question_edit")
     * @Template("LadbCoreBundle:Faq/Question:edit.html.twig")
     * @Security("has_role('ROLE_ADMIN')", statusCode=404, message="Not allowed (core_faq_question_edit)")
     */
    public function editAction($id)
    {
        $om = $this->getDoctrine()->getManager();
        $questionRepository = $om->getRepository(Question::CLASS_NAME);

        $question = $questionRepository->findOneByIdJoinedOnOptimized($id);
        if (is_null($question)) {
            throw $this->createNotFoundException('Unable to find Question entity (id=' . $id . ').');
        }

        $form = $this->createForm(QuestionType::class, $question);

        $tagUtils = $this->get(TagUtils::NAME);

        return array(
            'question'     => $question,
            'form'         => $form->createView(),
            'tagProposals' => $tagUtils->getProposals($question),
        );
    }

    /**
     * @Route("/{id}/update", requirements={"id" = "\d+"}, name="core_faq_question_update")
     * @Method("POST")
     * @Template("LadbCoreBundle:Faq/Question:edit.html.twig")
     * @Security("has_role('ROLE_ADMIN')", statusCode=404, message="Not allowed (core_faq_question_update)")
     */
    public function updateAction(Request $request, $id)
    {
        $om = $this->getDoctrine()->getManager();
        $questionRepository = $om->getRepository(Question::CLASS_NAME);

        $question = $questionRepository->findOneByIdJoinedOnOptimized($id);
        if (is_null($question)) {
            throw $this->createNotFoundException('Unable to find Question entity (id=' . $id . ').');
        }

        $originalBodyBlocks = $question->getBodyBlocks()->toArray();    // Need to be an array to copy values
        $previouslyUsedTags = $question->getTags()->toArray();  // Need to be an array to copy values

        $question->resetBodyBlocks(); // Reset bodyBlocks array to consider form bodyBlocks order

        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isValid()) {

            $blockUtils = $this->get(BlockBodiedUtils::NAME);
            $blockUtils->preprocessBlocks($question, $originalBodyBlocks);

            $fieldPreprocessorUtils = $this->get(FieldPreprocessorUtils::NAME);
            $fieldPreprocessorUtils->preprocessFields($question);

            $question->setUpdatedAt(new \DateTime());

            $om->flush();

            // Dispatch publication event
            $dispatcher = $this->get('event_dispatcher');
            $dispatcher->dispatch(PublicationListener::PUBLICATION_UPDATED, new PublicationEvent($question, array( 'previouslyUsedTags' => $previouslyUsedTags )));

            // Flashbag
            $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('faq.question.form.alert.update_success', array( '%title%' => $question->getTitle() )));

            // Regenerate the form
            $form = $this->createForm(QuestionType::class, $question);

        } else {

            // Flashbag
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('default.form.alert.error'));

        }

        $tagUtils = $this->get(TagUtils::NAME);

        return array(
            'question'     => $question,
            'form'         => $form->createView(),
            'tagProposals' => $tagUtils->getProposals($question),
        );
    }

    /**
     * @Route("/{id}/delete", requirements={"id" = "\d+"}, name="core_faq_question_delete")
     * @Security("has_role('ROLE_ADMIN')", statusCode=404, message="Not allowed (core_faq_question_delete)")
     */
    public function deleteAction($id)
    {
        $om = $this->getDoctrine()->getManager();
        $questionRepository = $om->getRepository(Question::CLASS_NAME);

        $question = $questionRepository->findOneById($id);
        if (is_null($question)) {
            throw $this->createNotFoundException('Unable to find Question entity (id=' . $id . ').');
        }

        // Delete
        $questionManager = $this->get(QuestionManager::NAME);
        $questionManager->delete($question);

        // Flashbag
        $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('faq.question.form.alert.delete_success', array( '%title%' => $question->getTitle() )));

        return $this->redirect($this->generateUrl('core_faq_question_list'));
    }

    /**
     * @Route("/{filter}", requirements={"filter" = "[a-z-]+"}, name="core_faq_question_list_filter")
     * @Route("/{filter}/{page}", requirements={"filter" = "[a-z-]+", "page" = "\d+"}, name="core_faq_question_list_filter_page")
     */
    public function goneListAction(Request $request, $filter, $page = 0)
    {
        throw new \Symfony\Component\HttpKernel\Exception\GoneHttpException();
    }

    /**
     * @Route("/", name="core_faq_question_list")
     * @Route("/{page}", requirements={"page" = "\d+"}, name="core_faq_question_list_page")
     * @Template("LadbCoreBundle:Faq/Question:list.html.twig")
     */
    public function listAction(Request $request, $page = 0)
    {
        $searchUtils = $this->get(SearchUtils::NAME);

        // Elasticsearch paginiation limit
        if ($page > 624) {
            throw $this->createNotFoundException('Page limit reached (core_faq_question_list_page)');
        }

        $searchParameters = $searchUtils->searchPaginedEntities(
            $request,
            $page,
            function ($facet, &$filters, &$sort, &$noGlobalFilters, &$couldUseDefaultSort) {
                switch ($facet->name) {

                    // Filters /////

                    case 'mine':
                        if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {

                            if ($facet->value == 'draft') {

                                $filter = (new \Elastica\Query\BoolQuery())
                                    ->addFilter(new \Elastica\Query\MatchPhrase('user.username', $this->getUser()->getUsername()))
                                    ->addFilter(new \Elastica\Query\Range('visibility', array( 'lt' => HiddableInterface::VISIBILITY_PUBLIC )))
                                ;

                            } else {

                                $filter = new \Elastica\Query\MatchPhrase('user.username', $this->getUser()->getUsernameCanonical());
                            }

                            $filters[] = $filter;

                            $couldUseDefaultSort = true;

                        }

                        break;

                    case 'tag':
                        $filter = new \Elastica\Query\QueryString($facet->value);
                        $filter->setFields(array( 'tags.label' ));
                        $filters[] = $filter;

                        break;

                    // Sorters /////

                    case 'sort-recent':
                        $sort = array( 'changedAt' => array( 'order' => 'desc' ) );
                        break;

                    case 'sort-popular-views':
                        $sort = array( 'viewCount' => array( 'order' => 'desc' ) );
                        break;

                    case 'sort-popular-likes':
                        $sort = array( 'likeCount' => array( 'order' => 'desc' ) );
                        break;

                    case 'sort-popular-comments':
                        $sort = array( 'commentCount' => array( 'order' => 'desc' ) );
                        break;

                    case 'sort-random':
                        $sort = array( 'randomSeed' => isset($facet->value) ? $facet->value : '' );
                        break;

                    case 'sort-important':
                        $sort = array( 'weight' => array( 'order' => 'desc' ) );
                        break;

                    /////

                    default:
                        if (is_null($facet->name)) {

                            $filter = new \Elastica\Query\QueryString($facet->value);
                            $filter->setFields(array( 'title^100', 'body', 'tags.label' ));
                            $filters[] = $filter;

                        }

                }
            },
            function (&$filters, &$sort) {

                $sort = array( 'weight' => array( 'order' => 'desc' ) );

            },
            function (&$filters) {

                $user = $this->getUser();
                $publicVisibilityFilter = new \Elastica\Query\Range('visibility', array( 'gte' => HiddableInterface::VISIBILITY_PUBLIC ));
                if (!is_null($user)) {

                    $filter = new \Elastica\Query\BoolQuery();
                    $filter->addShould(
                        $publicVisibilityFilter
                    );
                    $filter->addShould(
                        (new \Elastica\Query\BoolQuery())
                            ->addFilter(new \Elastica\Query\MatchPhrase('user.username', $user->getUsername()))
                            ->addFilter(new \Elastica\Query\Range('visibility', array( 'gte' => HiddableInterface::VISIBILITY_PRIVATE )))
                    );

                } else {
                    $filter = $publicVisibilityFilter;
                }
                $filters[] = $filter;


            },
            'fos_elastica.index.ladb.faq_question',
            \Ladb\CoreBundle\Entity\Faq\Question::CLASS_NAME,
            'core_faq_question_list_page'
        );

        // Dispatch publication event
        $dispatcher = $this->get('event_dispatcher');
        $dispatcher->dispatch(PublicationListener::PUBLICATIONS_LISTED, new PublicationsEvent($searchParameters['entities']));

        $parameters = array_merge($searchParameters, array(
            'questions' => $searchParameters['entities'],
        ));

        if ($request->isXmlHttpRequest()) {
            return $this->render('LadbCoreBundle:Faq/Question:list-xhr.html.twig', $parameters);
        }

        return $parameters;
    }

    /**
     * @Route("/{id}.html", requirements={"slug" = "[a-z-]+"}, name="core_faq_question_show")
     * @Template("LadbCoreBundle:Faq/Question:show.html.twig")
     */
    public function showAction(Request $request, $id)
    {
        $om = $this->getDoctrine()->getManager();
        $questionRepository = $om->getRepository(Question::CLASS_NAME);
        $witnessManager = $this->get(WitnessManager::NAME);

        if (intval($id) == 0) {
            $question = $questionRepository->findOneBySlugJoinedOnAll($id);
        } else {
            $id = intval($id);
            $question = $questionRepository->findOneById($id);
        }
        if (is_null($question)) {
            if ($response = $witnessManager->checkResponse(Question::TYPE, $id)) {
                return $response;
            }
            throw $this->createNotFoundException('Unable to find Question entity (id=' . $id . ').');
        }
        if ($question->getIsDraft() === true) {
            if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
                if ($response = $witnessManager->checkResponse(Question::TYPE, $id)) {
                    return $response;
                }
                throw $this->createNotFoundException('Not allowed (core_faq_question_show)');
            }
        }

        $explorableUtils = $this->get(ExplorableUtils::NAME);
        $similarQuestions = $explorableUtils->getSimilarExplorables($question, 'fos_elastica.index.ladb.faq_question', Question::CLASS_NAME, null, 10);

        // Dispatch publication event
        $dispatcher = $this->get('event_dispatcher');
        $dispatcher->dispatch(PublicationListener::PUBLICATION_SHOWN, new PublicationEvent($question));

        $likableUtils = $this->get(LikableUtils::NAME);
        $watchableUtils = $this->get(WatchableUtils::NAME);
        $commentableUtils = $this->get(CommentableUtils::NAME);
        $followerUtils = $this->get(FollowerUtils::NAME);

        return array(
            'question'         => $question,
            'similarQuestions' => $similarQuestions,
            'likeContext'      => $likableUtils->getLikeContext($question, $this->getUser()),
            'watchContext'     => $watchableUtils->getWatchContext($question, $this->getUser()),
            'commentContext'   => $commentableUtils->getCommentContext($question),
            'followerContext'  => $followerUtils->getFollowerContext($question->getUser(), $this->getUser()),
        );
    }
}
