<?php

namespace Ladb\CoreBundle\Manager\Wonder;

use Ladb\CoreBundle\Entity\Wonder\Plan;

class PlanManager extends AbstractWonderManager
{

    const NAME = 'ladb_core.plan_manager';

    /////

    public function publish(Plan $plan, $flush = true)
    {

        $plan->getUser()->getMeta()->incrementPrivatePlanCount(-1);
        $plan->getUser()->getMeta()->incrementPublicPlanCount();

        // Creations counter update
        foreach ($plan->getCreations() as $creation) {
            $creation->incrementPlanCount(1);
        }

        // Workshops counter update
        foreach ($plan->getWorkshops() as $workshop) {
            $workshop->incrementPlanCount(1);
        }

        // Howtos counter update
        foreach ($plan->getHowtos() as $howto) {
            $howto->incrementPlanCount(1);
        }

        // Inspirations counter update
        foreach ($plan->getInspirations() as $inspiration) {
            $inspiration->incrementReboundCount(1);
        }

        parent::publishPublication($plan, $flush);
    }

    public function unpublish(Plan $plan, $flush = true)
    {

        $plan->getUser()->getMeta()->incrementPrivatePlanCount(1);
        $plan->getUser()->getMeta()->incrementPublicPlanCount(-1);

        // Creations counter update
        foreach ($plan->getCreations() as $creation) {
            $creation->incrementPlanCount(-1);
        }

        // Workshops counter update
        foreach ($plan->getWorkshops() as $workshop) {
            $workshop->incrementPlanCount(-1);
        }

        // Howtos counter update
        foreach ($plan->getHowtos() as $howto) {
            $howto->incrementPlanCount(-1);
        }

        // Inspirations counter update
        foreach ($plan->getInspirations() as $inspiration) {
            $inspiration->incrementReboundCount(-1);
        }

        parent::unpublishPublication($plan, $flush);
    }

    public function delete(Plan $plan, $withWitness = true, $flush = true)
    {

        // Decrement user plan count
        if ($plan->getIsDraft()) {
            $plan->getUser()->getMeta()->incrementPrivatePlanCount(-1);
        } else {
            $plan->getUser()->getMeta()->incrementPublicPlanCount(-1);
        }

        // Unlink creations
        foreach ($plan->getCreations() as $creation) {
            $creation->removePlan($plan);
        }

        // Unlink workshops
        foreach ($plan->getWorkshops() as $workshop) {
            $workshop->removePlan($plan);
        }

        // Unlink howtos
        foreach ($plan->getHowtos() as $howto) {
            $howto->removePlan($plan);
        }

        // Unlink inspirations
        foreach ($plan->getInspirations() as $inspiration) {
            $plan->removeInspiration($inspiration);
        }

        parent::deleteWonder($plan, $withWitness, $flush);
    }
}
