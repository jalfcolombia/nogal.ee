<?php

$skeleton = <<<skeleton
<?php

namespace Model;

use Model\Base\\{$table}Base;

class {$table} extends {$table}Base
{

    public function __construct()
    {
        parent::__construct(\$GLOBALS['config']);
    }

}

skeleton;
