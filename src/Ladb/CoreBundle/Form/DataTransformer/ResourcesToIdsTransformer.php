<?php

namespace Ladb\CoreBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Doctrine\Common\Persistence\ObjectManager;
use Ladb\CoreBundle\Entity\Core\Resource;

class ResourcesToIdsTransformer implements DataTransformerInterface
{

    private $om;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    public function transform($resources)
    {
        if (null === $resources) {
            return '';
        }

        if (!$resources instanceof \Doctrine\Common\Collections\Collection) {
            throw new UnexpectedTypeException($resources, '\Doctrine\Common\Collections\Collection');
        }

        $idsArray = array();
        foreach ($resources as $resources) {
            $idsArray[] = $resources->getId();
        }
        return implode(',', $idsArray);
    }

    public function reverseTransform($idsString)
    {
        if (!$idsString) {
            return array();
        }

        $resources = array();
        $idsStrings = preg_split("/[,]+/", $idsString);
        $repository = $this->om->getRepository(Resource::CLASS_NAME);
        foreach ($idsStrings as $idString) {
            $id = intval($idString);
            if ($id == 0) {
                continue;
            }
            $resource = $repository->find($id);
            if (is_null($resource)) {
                throw new TransformationFailedException();
            }
            $resources[] = $resource;
        }

        return $resources;
    }
}
