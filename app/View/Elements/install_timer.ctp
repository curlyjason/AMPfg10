<?php
if ($load) {
    $this->start('script');
    echo $this->Html->script(array('idle-timer', 'timer'));
    $this->end();
}
$this->start('jsGlobalVars');
echo "var idleLimit = {$timerParams['idleLimit']};";
echo "var warningLimit = {$timerParams['warningLimit']};";
$this->end();
?>
<!--<div class="timeWarning">
    <p>You've been idle for <?php echo ($timerParams['idleLimit'] / 60) ?> minutes.<br />
    You will be logged out in <?php echo ($timerParams['idleLimit'] / 60) ?> minutes.</p>
    <p>Do you wish to continue working?</p>
    <?php
//    echo $this->Form->input('No', array(
//        'type' => 'button',
//        'id' => 'timeWarningNo',
//	'label' => false
//    ));
//    echo $this->Form->input('Yes', array(
//        'type' => 'button',
//        'id' => 'timeWarningYes',
//	'label' => false
//    ));
    ?>
</div>-->
