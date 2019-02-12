<?php

declare(strict_types=1);

namespace App\BasicRum\Layers\DataLayer\Query;

use App\BasicRum\Layers\DataLayer\Query\Condition;
use App\BasicRum\Layers\DataLayer\Query\Select;

class Planner
{

    private $startPeriod;

    private $endPeriod;

    /** @var array */
    private $requirements = [];

    public function __construct(
        string $startPeriod,
        string $endPeriod,
        array $requirements
    )
    {
        $this->startPeriod  = $startPeriod;
        $this->endPeriod    = $endPeriod;
        $this->requirements = $requirements;
    }


    /**
     * @return Plan
     */
    public function createPlan()
    {
        $plan = new Plan('NavigationTimings');

        $between = new Condition\Between(
            'NavigationTimings',
            'createdAt',
            $this->startPeriod,
            $this->endPeriod
        );


        $plan->addPrefetchFilter(
            'NavigationTimings',
            'pageViewId',
            $between,
            new Select\Min(
                'NavigationTimings',
                'pageViewId'
            ),
            ">="
        );

        $plan->addPrefetchFilter(
            'NavigationTimings',
            'pageViewId',
            $between,
            $max = new Select\Max(
                'NavigationTimings',
                'pageViewId'
            ),
            "<="
        );


//        $minId = $this->_getLowesIdInInterval($registry, $this->startPeriod, $this->endPeriod);
//        $maxId = $this->_getHighestIdInInterval($registry, $this->startPeriod, $this->endPeriod);

//        $queryBuilder
//            ->select(['nt.' . $perfMetricCamelized])
//            ->where("nt.pageViewId >= '" . $minId . "' AND nt.pageViewId <= '" . $maxId . "'");

//        $select = [];
//
//        foreach ($this->requirements as $requirement) {
//
//            if ($requirement instanceof \App\BasicRum\Report\SelectableInterface ) {
////                $select[] = [$this->_getEntityNamePrefix($requirement->getEntity()) . '.' . $requirement->getDataField()];
//            }
//
//            if ($requirement instanceof \App\BasicRum\Report\FilterableInterface ) {
//
//            }
//        }

        return $plan;
    }

}