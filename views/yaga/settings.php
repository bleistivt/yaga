<?php if (!defined('APPLICATION')) exit();

use Yaga;

/* Copyright 2013-2014 Zachary Doll */

echo wrap($this->title(), 'h1');

echo $this->ConfigurationModule->toString();

echo wrap(t('Yaga.Transport'), 'h3');

echo wrap(t('Yaga.Transport.Desc'), 'div', ['class' => 'Wrap']);

echo wrap(
                anchor(
                                t('Import'),
                                'yaga/import',
                                ['class' => 'Button']
                                ) .
                anchor(
                                t('Export'),
                                'yaga/export',
                                ['class' => 'Button']),
                'div',
                [
                        'class' => 'Wrap']
                );

?>
