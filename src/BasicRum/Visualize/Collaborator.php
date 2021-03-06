<?php

declare(strict_types=1);

namespace App\BasicRum\Visualize;

class Collaborator implements \App\BasicRum\CollaboratorsInterface
{

    /**
     * @return string
     */
    public function getCommandParameterName() : string
    {
        return 'visualize';
    }

    /**
     * @param array $requirement
     * @return \App\BasicRum\CollaboratorsInterface
     */
    public function applyForRequirement(array $requirement) : \App\BasicRum\CollaboratorsInterface
    {

        return $this;
    }

    /**
     * @return array
     */
    public function getRequirements() : array
    {
        return [];
    }

    /**
     * @return array
     */
    public function getAllPossibleRequirementsKeys() : array
    {
        return array_keys([]);
    }

}