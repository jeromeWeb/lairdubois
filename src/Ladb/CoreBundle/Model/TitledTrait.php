<?php

namespace Ladb\CoreBundle\Model;

trait TitledTrait
{

    // Title /////

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }
}
