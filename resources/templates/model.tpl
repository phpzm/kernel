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

    /**
    * @return array
    */
    public function getDefaultsCreate(): array
    {
        /*return [
            '${{field1}}' => 'somethingvalue',
            '${{field2}}' => somethingvalue,
            '${{field3}}' => somethingvalue
            [, ...]
        ];*/
    }

    /**
    * @param string $action
    * @param Record $record
    * @param Record|null $previous
    * @return bool
    */
    public function before(string $action, Record $record, Record $previous = null): bool
    {
        /*if ($record->get('${{field}}') === 'something') {
            $record->set('${{field1}}', 'something1');
            $record->set('${{field2}}', somethingvalue);
            $record->set('${{field3andbeyond}}', somethingvalueandbeyond);
        }

        return parent::before($action, $record, $previous); */
    }
}
