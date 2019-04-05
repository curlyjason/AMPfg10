<?php
/**
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.View.Layouts
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
$cakeDescription = __d('cake_dev', 'AMP FG System - Login');
$title_for_layout = 'Login';

// create global variables set for javascript paths
$this->start('jsGlobalVars');
echo "var webroot = '{$this->request->webroot}';";
echo "var action = '{$this->request->params['action']}/';";
echo "var controller = '$controller/';";
$this->end();
?>
<!DOCTYPE html>
<html>
    <head>
        <?php echo $this->Html->charset(); ?>
        <title>
            <?php echo $cakeDescription ?>:
            <?php echo $title_for_layout; ?>
        </title>
        <?php
        echo $this->Html->meta('icon');
        echo $this->Html->css(array('cake.generic', 'ampfg', 'menu', 'budget', 'help'));
        echo $this->Html->script(array($jquery, 'jquery-ui', 'app', 'budget', 'help'));

        echo $this->fetch('meta');
        echo $this->fetch('css');
        echo $this->fetch('script');
            ?>
<?php
// Javascript often needs to construct a path but doesn't have access to needed
// environmental or contextual values. Global vars here can fix that.
        echo "<script type=\"text/javascript\">
//<![CDATA[
// global data for javascript\r";
        echo $this->fetch('jsGlobalVars');
        echo"\r//]]>
</script>";
?>
		
    </head>
    <body>
        <!--        The defeat div is used to cover all elements of the page
                In a timeout or session ending dialog event-->
        <div id="defeat" class="hide">
        </div>
        <div id="container">
            <?php 
//			$this->element('header', array('cakeDescription' => $cakeDescription, 'adminLoginUsers' => $adminLoginUsers));
			echo $this->Html->div('header', NULL, array('id' => 'header'));
			echo $this->FgHtml->image('AMP_PrintResp_logo_300.png');
			echo $this->Html->tag('h1', $this->Html->link(' AMP FG System - Login', 'http://www.ampprinting.com'));
//			echo $this->fetch('header');
//			echo $this->element('menu', $menuItems);
			echo '</div>';
			?>

            <div id="content">
                <?php
				echo $this->Session->flash();
				echo $this->Session->flash('auth');
                if (isset($pageHeading)) {
                    echo $this->Html->tag('h1', $pageHeading);
                }
                echo $this->fetch('content');
                ?>
            </div>
        </div>
    </body>
</html>
