<?php
/**
 * Created by PhpStorm.
 * User: jasont
 * Date: 2019-02-06
 * Time: 11:38
 */
App::uses('XMLStatus','Lib');
App::uses('JSONStatus','Lib');
App::uses('RobotPackets', 'Lib');

class RobotStatusPackets extends RobotPackets
{
    function migrateRequest($input, $mode){}
    function getResponse(){}
    function marshallPackets(){}

}