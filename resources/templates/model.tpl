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
            '${{field1}}' => 'value',
            '${{field2}}' => value,
            '${{field3}}' => value
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
        /*if ($record->get('${{field}}') === 'value') {
            $record->set('${{field1}}', 'value');
        }

        return parent::before($action, $record, $previous); */
    }
}
