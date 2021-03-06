<?php

declare(strict_types=1);

namespace App\BasicRum\Visit;

use App\BasicRum\Visit\Data\Fetch;

use App\BasicRum\Visit\Calculator\Aggregator;

class Calculator
{

    /** @var int */
    private $scannedChunkSize     = 1000;

    /** @var int */
    private $sessionExpireMinutes = 30;

    /** @var \Symfony\Bridge\Doctrine\RegistryInterface */
    private $registry;

    /** @var Fetch */
    private $fetch;

    /** @var Aggregator */
    private $aggregator;

    public function __construct(\Symfony\Bridge\Doctrine\RegistryInterface $registry)
    {
        $this->registry   = $registry;
        $this->fetch      = new Fetch($registry);
        $this->aggregator = new Aggregator($this->sessionExpireMinutes, $this->fetch);
    }

    /**
     * @return array
     */
    public function calculate() : array
    {
        $lastPageViewId = $this->fetch->fetchPreviousLastScannedPageViewId();

        $navTimingsRes = $this->fetch->fetchNavTimingsInRange($lastPageViewId + 1, $lastPageViewId + $this->scannedChunkSize);

        $notCompletedVisits = $this->fetch->fetchNotCompletedVisits();

        foreach ($notCompletedVisits as $notCompletedVisit)
        {
            $notCompletedViews = $this->fetch->fetchNavTimingsInRangeForSession(
                $notCompletedVisit['firstPageViewId'],
                $notCompletedVisit['lastPageViewId'],
                $notCompletedVisit['guid']
            );

            foreach ($notCompletedViews as $view) {
                $this->aggregator->addPageView($view);
            }
        }

        foreach ($navTimingsRes as $nav)
        {
            $this->aggregator->addPageView($nav);
        }

        $visits = $this->aggregator->generateVisits($notCompletedVisits);

        return $visits;
    }

}