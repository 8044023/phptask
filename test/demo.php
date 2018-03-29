<?php
use phpTask\Task;
/** 
 * phpTask
 * @author     邹霞<986830185@qq.com>
 * @copyright  taskPHP
 * @license    https://github.com/8044023/phptask
 */
include '../src/Phptask/Task.php';
$config=[];
$task=new Task($config);
$task->addTask(function(){
 echo "2秒执行一次";
}, "任务标题", "2@s");//2秒执行一次
$task->addTask(function(){
 echo "2分钟执行一次";
}, "任务标题", "2@i");//2分钟执行一次
$task->addTask(function(){
 echo "2小时执行一次";
}, "任务标题", "2@h");//2小时执行一次

$task->start();
