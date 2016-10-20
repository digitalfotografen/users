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

use Cake\Core\Configure;

?>
<div class="smsky form">
    <?= $this->Flash->render('auth') ?>
    <?= $this->Form->create() ?>
    <fieldset>
        <legend><?= __d('CakeDC/Users', 'Please enter sms key') ?></legend>
        <?= $this->Form->input('smskey', ['required' => true]) ?>
        <?= $this->Html->link(__d('CakeDC/Users', 'Resend SMS key'), '/login',
            ['class' => 'button']
        ); ?>
    </fieldset>
    <?= $this->Form->button(__d('CakeDC/Users', 'Login')); ?>
    <?= $this->Form->end() ?>
</div>
