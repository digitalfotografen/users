<?php
/**
 * Copyright 2010 - 2015, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2015, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

use Phinx\Migration\AbstractMigration;

class Smskey extends AbstractMigration
{
    public function up()
    {
        $table = $this->table('users');
        $table->addColumn('sms', 'string', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->update();
    }

    public function down()
    {
        $table = $this->table('users');
        $table->removeColumn('sms');
        $table->update();
    }
}
