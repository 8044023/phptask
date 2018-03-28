phptask v1.0.0 —— 你值得信赖的PHP定时计划任务插件
===============


## taskPHP
phptask基于php开发的定时计划任务插件,支持linux。有较好的伸缩性、扩展性、健壮稳定性而被多家公司使用，同时也希望开源爱好者一起贡献。<br>
## 项目地址
github地址: https://github.com/qq8044023/taskPHP<br>
oschina地址: http://git.oschina.net/cqcqphper/taskPHP<br>
这两个地址都会同步更新。
## 在线交流QQ群

如感兴趣请加QQ群 一起探讨、完善。越多人支持,就越有动力去更新,喜欢记得右上角star哈。<br>



## 环境要求
1. php版本>= 5.6<br>

   
## 注意事项
1. 由于任务存在派发时间，所以任务运行的时间可能会有1-2秒的误差。
2. 编写任务有问题或调用exit将导致后台脚本停止,需要通过远控管理器重启进程。
3. 多线程模式运行一段时间后报错,pthreads has detected that the core\lib\Pthread could not be started, the system lacks the necessary resources or the system-imposed limit would be exceeded in xxx
4. 后台任务数量多或者任务运行时间很密集导致数据库链接过多没有释放,需要再任务结尾处执行数据库链接对象的close方法来关闭链接。



## 使用说明

### composer安装taskphp框架:
``` php
composer require phptask/phptask
```
## 命令操作
``` php
test.php  start 启动
test.php  start -d	挂载后台运行
test.php  status 查看任务列表
test.php  close 关闭

```
### Liunx挂载后台启动
``` php
[root@iZuf63dqaawoexc4lalmfsZ www.chat.com]# php test.php start
----------------------- taskPHP --------------------------------
timePHP version:v1.0          PHP version:7.0.21
startTime:2018-03-28 10:08:18
------------------------ timePHP -------------------------------
名称                          时间                          进程数
支付宝红包自动退回     5@s                               1
----------------------------------------------------------------

```

### Liunx挂载后台运行
``` php
[root@iZuf63dqaawoexc4lalmfsZ www.chat.com]# php test.php start -d
----------------------- taskPHP --------------------------------
timePHP version:v1.0          PHP version:7.0.21
startTime:2018-03-28 10:08:18
------------------------ timePHP -------------------------------
名称                          时间                          进程数
支付宝红包自动退回     5@s                               1
----------------------------------------------------------------

```

### Liunx查看任务列表
``` php
[root@iZuf63dqaawoexc4lalmfsZ www.chat.com]# php test.php status
----------------------- taskPHP --------------------------------
timePHP version:v1.0          PHP version:7.0.21
startTime:2018-03-28 10:08:18
------------------------ timePHP -------------------------------
名称                          时间                          进程数
支付宝红包自动退回     5@s                               1
----------------------------------------------------------------

``` 

### Liunx关闭
``` php
[root@iZuf63dqaawoexc4lalmfsZ www.chat.com]# php test.php close
[关闭成功]

``` 

### 普通使用
``` php
use Taskphp\Task;
/**
 * phpTask
 * @author     邹霞<986830185@qq.com>
 * @copyright  taskPHP
 * @license    https://github.com/8044023/phptask
 */
include '../src/Phptask/Task.php';
$task=new Task();
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

```

### 整合thinkphp5

``` php
namespace app\api\command;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use Pagerfanta\Task;
class Tasks extends Command{
    protected function configure(){
        $task=new Task(["frame"=>"think5"]);
        $task->addTask(function(){
           echo "需要运行的代码";;
        }, "支付宝红包自动退回", "5@s");
        $task->start();
        //$this->setName('Tasks')->setDescription('Here is the remark ');
    }

    protected function execute(Input $input, Output $output){
        $output->writeln("TestCommand:");
    }
}

```
