<?php if (!defined('APPLICATION')) exit();

$actionName = $this->data('ActionName');
$otherActions = $this->data('OtherActions', null);

echo wrap($this->data('Title'), 'h1');

echo $this->Form->open();
echo $this->Form->errors();

echo wrap(
    $this->Form->checkbox('Move', sprintf(Gdn::translate('Yaga.Action.Move'), $actionName)).' '.$this->Form->dropDown('ReplacementID', $otherActions), 'div', ['class' => 'Info']);
echo wrap(
    sprintf(Gdn::translate('Are you sure you want to delete this %s?'), $actionName.' '.Gdn::translate('Yaga.Action')).
        wrap(
            $this->Form->button('OK', ['class' => 'Button Primary']).
            $this->Form->button('Cancel', ['type' => 'button', 'class' => 'Button Close']), 'div', ['class' => 'Buttons Buttons-Confirm']
        ), 'div', ['class' => 'Info']);

echo $this->Form->close();
