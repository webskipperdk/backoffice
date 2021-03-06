<?php

declare(strict_types=1);

namespace App\BasicRum\Layers\DataLayer\Query;

class Plan
{

    /** @var string */
    private $mainTableName;

    /** @var array */
    private $secondaryFilters = [];

    /** @var array */
    private $primaryFilters   = [];

    /** @var array */
    private $selects          = [];

    /** @var array */
    private $complexSelects   = [];

    /** @var MainDataSelect\MainDataInterface */
    private $dataFlavor;

    /** @var array */
    private $limiterFilters   = [];

    /**
     * Plan constructor.
     * @param string $mainTableName
     * @param MainDataSelect\MainDataInterface $dataFlavor
     */
    public function __construct(
        string $mainTableName,
        MainDataSelect\MainDataInterface $dataFlavor)
    {
        $this->mainTableName = $mainTableName;
        $this->dataFlavor    = $dataFlavor;
    }

    /**
     * @param SelectInterface $select
     * @return Plan
     */
    public function addSelect(SelectInterface $select) : self
    {
        $this->selects[] = new Plan\Select($select);

        return $this;
    }

    /**
     * @param string $primarySelectTableName
     * @param string $primaryKeyFieldName
     * @param string $secondarySelectTableName
     * @param string $secondaryKeyFieldName
     * @param array $secondarySelectDataFieldNames
     * @return Plan
     */
    public function addComplexSelect(
        string $primarySelectTableName,
        string $primaryKeyFieldName,
        string $secondarySelectTableName,
        string $secondaryKeyFieldName,
        array $secondarySelectDataFieldNames
    ) : self
    {
        $this->complexSelects[] = new Plan\ComplexSelect(
            $primarySelectTableName,
            $primaryKeyFieldName,
            $secondarySelectTableName,
            $secondaryKeyFieldName,
            $secondarySelectDataFieldNames
        );

        return $this;
    }

    /**
     * @param string $primaryTableName
     * @param string $primarySearchFieldName
     * @param string $secondaryTableName
     * @param ConditionInterface $prefetchCondition
     * @param SelectInterface $prefetchSelect
     * @param string $mainCondition
     * @return Plan
     */
    public function addLimiterFilter(
        string $primaryTableName,
        string $primarySearchFieldName,
        string $secondaryTableName,
        \App\BasicRum\Layers\DataLayer\Query\ConditionInterface $prefetchCondition,
        \App\BasicRum\Layers\DataLayer\Query\SelectInterface $prefetchSelect,
        string $mainCondition
    ) : self
    {
        $this->limiterFilters[] = new Plan\SecondaryFilter(
            $primaryTableName,
            $primarySearchFieldName,
            $secondaryTableName,
            $prefetchCondition,
            $prefetchSelect,
            $mainCondition
        );

        return $this;
    }

    /**
     * @param string $primaryTableName
     * @param string $primarySearchFieldName
     * @param string $secondaryTableName
     * @param ConditionInterface $prefetchCondition
     * @param SelectInterface $prefetchSelect
     * @param string $mainCondition
     * @return Plan
     */
    public function addSecondaryFilter(
        string $primaryTableName,
        string $primarySearchFieldName,
        string $secondaryTableName,
        \App\BasicRum\Layers\DataLayer\Query\ConditionInterface $prefetchCondition,
        \App\BasicRum\Layers\DataLayer\Query\SelectInterface $prefetchSelect,
        string $mainCondition
    ) : self
    {
        $this->secondaryFilters[] = new Plan\SecondaryFilter(
                $primaryTableName,
                $primarySearchFieldName,
                $secondaryTableName,
                $prefetchCondition,
                $prefetchSelect,
                $mainCondition
            );

        return $this;
    }

    /**
     * @param string $primaryTableName
     * @param string $primarySearchFieldName
     * @param string $mainCondition
     * @return Plan
     */
    public function addPrimaryFilter(
        string $primaryTableName,
        string $primarySearchFieldName,
        \App\BasicRum\Layers\DataLayer\Query\ConditionInterface $condition
    ) : self
    {
        $this->primaryFilters[] = new Plan\PrimaryFilter(
                $primaryTableName,
                $primarySearchFieldName,
                $condition
            );

        return $this;
    }

    /**
     * @return array
     */
    public function releasePlan() : array
    {
        return [
            'main_table_name'  => $this->mainTableName,
            'data_flavor'      => $this->dataFlavor,
            'selects'          => $this->selects,
            'complex_selects'  => $this->complexSelects,
            'partial_queries'  => $this->complexSelects,
            'where'  => [
                'primaryFilters'   => $this->primaryFilters,
                'secondaryFilters' => $this->secondaryFilters,
                'limitFilters'     => $this->limiterFilters
            ]
        ];
    }

}