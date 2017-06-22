<?php

namespace ${NAMESPACE}\Model;

use Simples\Data\Record;
use Simples\Model\DataMapper;

/**
 * Class ${NAME}
 * @package ${NAMESPACE}\Model
 */
class ${NAME} extends DataMapper
{
    /**
     * ${NAME} constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->configure('${table}', '${primaryKey}');

        $this->add('${primaryKey}')->integer();
        $this->add('${description}')->string()->required();
    }
}
