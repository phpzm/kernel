<?php

namespace ${NAMESPACE}\Repository;

use ${NAMESPACE}\Model\${NAME};
use Simples\Model\Repository\ModelRepository;

/**
 * Class ${NAME}Repository
 * @package ${NAMESPACE}\Repository
 */
class ${NAME}Repository extends ModelRepository
{
    /**
     * @var ${NAME}
     */
    protected $model;

    /**
     * ${NAME}Repository constructor.
     * @param ${NAME} $model
     */
    public function __construct(${NAME} $model)
    {
        parent::__construct($model);
    }
}
