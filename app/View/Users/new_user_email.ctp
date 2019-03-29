<?php

$tempLink = $this->Html->link('Dashboard', array(
    'controller' => 'Users', 'action' => 'emailLogin', $email, $password)
);
echo 'Click this link to login to AMP FG' + "\r" + $tempLink + "\r" + 'you\'ll need to reset your password when you arrive.';

?>