<?php if (!defined('APPLICATION')) exit();

use Yaga;

/* Copyright 2013-2014 Zachary Doll */

echo wrap($this->title(), 'h1');

echo $this->ConfigurationModule->toString();

echo wrap(Gdn::translate('Yaga.Transport'), 'h3');

echo wrap(Gdn::translate('Yaga.Transport.Desc'), 'div', ['class' => 'Wrap']);

echo wrap(
                anchor(
                                Gdn::translate('Import'),
                                'yaga/import',
                                ['class' => 'Button']
                                ).
                anchor(
                                Gdn::translate('Export'),
                                'yaga/export',
                                ['class' => 'Button']),
                'div',
                [
                        'class' => 'Wrap']
                );

?>
