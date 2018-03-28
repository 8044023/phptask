<?php
/**
 * phpTask
 * @author     邹霞<8044023@qq.com>
 * @copyright  taskPHP
 * @license    https://github.com/8044023/phptask
 */
namespace Phptask;
class Task{
    public $task_list=[];
    public static $_config=[];
    public static $_isstart=true;
    public function __construct($config){
        self::$_config=$config;
        Command::run();
    }
    /**
     * 添加任务
     * @param callable $callbel
     * @param unknown $name
     * @param unknown $time
     * @param number $course  */
    public function addTask(callable $callbel,$name,$time,$course=1){
        //命令解析  时间解析   进程分配
        $this->task_list[]=[
            "name"      =>$name,
            "time"      =>$time,
            "task"      =>$callbel,
            "course"    =>$course
        ];
    }
    /**
     * 启动
     *   */
    public function start(){
        if (Command::$_command==Command::_START_ && self::$_isstart){
            foreach ($this->task_list as $task)   Worker::run($task);
        }
    }
    public function __destruct(){
        if (Command::$_command==Command::_START_ && self::$_isstart){
            Log::writePid($this->task_list,Log::LOG_TASK);
            Ui::statusUI(false);
        }
        Worker::monitoringHostDaemon(posix_getpid());
        Worker::mountDaemon();
    }
}
//命令解析
class Command{
    public static $_isdaemon=false;
    public static $_command;
    const _START_       ="start";
    const _STATUS_      ="status";
    const _COLSE_       ="close";
    const _MY_VERSION_  ="v1.0";    
    public static function run(){
        //获取命令行参数
        $argv = self::analysisCommand();
        $start_file = $argv[0];
        Worker::$is_system=strtoupper(substr(PHP_OS,0,3));
        if (!isset($argv[1])) {
            Ui::statusUI("Usage: php yourfile.php {start|status|close}\n");
        }
        $command  = trim($argv[1]);
        $command2 = isset($argv[2]) ? $argv[2] : '';
        //验证 是否重复启动进程
        switch ($command){
            case self::_START_;
                self::regIsStart();
                if (Task::$_isstart==false){
                    Ui::displayUI("已经启动,请关闭后在启动");
                }
                Log::writePid(posix_getpid());
                break;
            case self::_STATUS_:
                Ui::statusUI();
                break;
            case self::_COLSE_:
                Worker::closeMonitoring();
                Ui::displayUI("关闭成功");
                break;
            default:
                Ui::displayUI("命令不存在");
                break;
        }
        self::$_command=$command;
        $command2=="-d" && self::$_isdaemon=true;
    }
    protected static function analysisCommand(){
        $argv = $_SERVER['argv'];
        if (!empty(Task::$_config)){
            switch (Task::$_config["frame"]){
                case "think5":unset($argv[0]);break;
            }
        }
        return array_values($argv);
    }
    protected static function regIsStart(){
        (!empty(Log::readPid())) && Task::$_isstart=false;
    }
}
//进程管理分配
class Worker{
    public static $is_system;
    public static function run($task){
        if (self::$is_system=="WIN"){//win系统
            self::executeWin($task);return ;
        }
        self::executeLiunx($task);//liunx 系统
    }
    public static function executeLiunx($task){
        for($i=0;$i<$task["course"];$i++){
            $pid = pcntl_fork();//开启进程
            if ($pid==-1){
                ui::displayUI("进程启动失败");
            }else if($pid==0){//开启子进程
                posix_setsid () == - 1 && die( "分离失败" );//TODO  待写异常抛出
                //TODO  时间解析
                Log::writePid(posix_getpid());
                self::start($task);die;
            }else{}
        }
    }
    public static function executeWin($task){ echo "win";}
    //运行
    public static function start($task){        while (true) {            TaskTimer::init($task["time"]);
            call_user_func($task["task"]);
        }
    }
    public static function monitoringHostDaemon($host_pid){
        if (Command::$_isdaemon==false && self::$is_system!="WIN" && Command::$_command==Command::_START_ && self::$_isstart){
            $pid = pcntl_fork();//开启进程
            if($pid==0){//开启子进程
                posix_setsid () == - 1 && die( "分离失败" );
                Log::writePid(posix_getpid());
                while (true) {
                    $cmd = "ps aux  | awk '{print $2}' | grep ".$host_pid;
                    $ret = shell_exec("$cmd");
                    if (empty(str_replace(PHP_EOL, '', $ret))){
                        self::closeMonitoring();
                    }
                }die;
            }
        }
    }
    public static function closeMonitoring(){
        $pid_list=Log::readPid();
        Log::writePid();
        //关闭所有进程
        foreach ($pid_list as $v_pid) posix_kill($v_pid, SIGTERM);//关闭当前进程
    }
    public static function mountDaemon(){
        if (Command::$_isdaemon==false && Command::$_command==Command::_START_ && self::$_isstart){
            while (true) {}
        }
    }
}
//时间解析
class TaskTimer{
    public static $_units="second";
    public static function init($time){
        if(strpos($time,'@') !==false){
            self::start(self::analysisHIS($time));
        }
    }
    //解析时分秒
    public static function analysisHIS($time){
        $date   =explode("@",$time);
        $second =0;
        switch ($date[1]){
            case "h":$second=$date[0]*3600;break;
            case "i":$second=$date[0]*60;break;
            case "s":$second=$date[0];break;
        }
        return $second;
    }
    //启动延迟
    public static function start($second){
        if (self::$_units=="second"){//秒
            sleep($second);
        }else if(self::$_units=="millisecond"){//毫秒
            usleep($second*1000);
        }else if(self::$_units=="microsecond"){//微秒
            usleep($second*1000*1000);
        }
    }
}
class Ui{
    protected static $_maxPidLength=30;
    protected static $_maxNameLength=30;
    /**
     * 默认UI
     * @param unknown $text
     * @param string $isClose  */
    public static function displayUI($text,$isClose=true){
        echo PHP_EOL." [".$text."] ".PHP_EOL;
        $isClose==true && die;
    }
    public static function statusUI($isClose=true){
        echo "----------------------- phptask --------------------------------".PHP_EOL;
        echo 'phptask version:', "v1.0", "          PHP version:", PHP_VERSION,PHP_EOL;
        echo 'startTime:', date("Y-m-d H:i:s"),PHP_EOL;
        echo "------------------------ phptask -------------------------------".PHP_EOL;
        echo "名称", str_pad('',
        self::$_maxPidLength + 2 - strlen('名称')), "时间", str_pad('',
        self::$_maxNameLength + 2 - strlen('时间')), "进程数".PHP_EOL;
        foreach (Log::readPid(Log::LOG_TASK) AS $v){
            echo str_pad($v->name, self::$_maxPidLength + 2), str_pad($v->time,self::$_maxNameLength + 2),"  ".$v->course.PHP_EOL;
        }
        echo "----------------------------------------------------------------".PHP_EOL;
        $isClose==true && die;
    }
}
class Log{
    public static $pid_path ="pid.log";
    public static $task_path="task.log";
    const LOG_PID           =1;
    const LOG_TASK          =2;
    public static function writePid($val="",$type=1){
        if (!empty($val) && $type==self::LOG_PID){
            error_log ($val.",",  3 , __DIR__."/".self::$pid_path);return;
        }else if($type==self::LOG_TASK){
            error_log (json_encode($val),  3 , __DIR__."/".self::$task_path);return;
        }
        file_put_contents(__DIR__."/".self::$task_path, "");
        file_put_contents(__DIR__."/".self::$pid_path, "");
    }
    public static function readPid($type=1){
        if ($type==self::LOG_PID){
            $file_path=__DIR__."/".self::$pid_path;
            if(file_exists($file_path)){
                return explode(",",file_get_contents($file_path),-1);//将整个文件内容读入到一个字符串中
            } 
        }else if ($type==self::LOG_TASK){
            return json_decode(file_get_contents(__DIR__."/".self::$task_path));
        }
    }
}