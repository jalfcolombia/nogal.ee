<?php

$skeleton = <<<skeleton
<?php

namespace Model\Base;

use NogalEE\Table\Base;
use NogalEE\DataType\DateTime;
use NogalEE\NQL;
{$namespace_details}
abstract class {$tableCamelCase}Base extends Base
{
{$sequence}{$schema}
    public const TABLE = self::SCHEMA . '{$table}';
{$fields}{$length}{$type}{$columns2}
    public function __construct(array \$config)
    {
        parent::__construct(\$config);
{$detail}{$defaults}
    }
{$getters_and_setters}{$save}{$update}
}

skeleton;