<?php

namespace App\Form\Model;

/**
 * Class SearchModel
 * @package App\Form\Model
 */
class SearchModel
{
    /**
     * @var string
     */
    public $term;

    /**
     * @return mixed
     */
    public function getTerm()
    {
        return $this->term;
    }

    /**
     * @param $term
     * @return $this
     */
    public function setTerm($term)
    {
        $this->term = $term;

        return $this;
    }
}