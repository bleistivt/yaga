<?php if (!defined('APPLICATION')) exit();

echo heading($this->data('Title'));

echo $this->Form->open();
echo $this->Form->errors();

echo wrap(
    sprintf(Gdn::translate('Are you sure you want to delete this %s?'), Gdn::translate('Yaga.Rank')),
    'div',
    ['class' => 'padded']
);

echo '<div class="js-modal-footer form-footer">';
echo $this->Form->button('OK');
echo $this->Form->button('Cancel', ['type' => 'button', 'class' => 'btn btn-secondary js-modal-close']);
echo '</div>';

echo $this->Form->close();
