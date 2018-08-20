<?php

namespace Ladb\CoreBundle\Utils;

class ElasticaQueryUtils
{

    const NAME = 'ladb_core.elastica_query_utils';

    /////

    public function createShouldMatchQuery($field, $values)
    {
        $subQueries = explode(',', $values);
        $filter = new \Elastica\Query\BoolQuery();
        foreach ($subQueries as $subQuery) {
            $filter->addShould(new \Elastica\Query\Match($field, $subQuery));
        }
        return $filter;
    }
}
