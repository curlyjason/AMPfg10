<?php
$tempLink = $this->Html->link('login to AMP FG', array(
    'controller' => 'users', 'action' => 'registration', 'full_base' => true, $email, $password)
);
echo 'Click this link to ' . $tempLink . '. You\'ll need to reset your password when you arrive.';
echo $this->Html->para('',"username: $email");
echo $this->Html->para('',"password: $password");
?>
