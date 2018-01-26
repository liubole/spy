<?php
/**
 * User: Tricolor
 * Date: 2018/1/26
 * Time: 17:47
 */
namespace Tricolor\Spy;

class Inspect
{
    private $output_dir = "/tmp/phptrace";
    private $output_file;
    private $cmd;
    private $spid;
    private $tmpfile;
    private $trace = true;
    private $pipes;

    /**
     * @var Inspect
     */
    private static $instance;
    public static function start($output = null, $cmd = 'phptrace -p %s')
    {
        self::$instance = new Inspect($output, $cmd);
        self::$instance->run();
        return self::$instance;
    }

    private function __construct($output = null, $cmd = 'phptrace -p %s')
    {
        if (!extension_loaded('trace') || !function_exists('register_shutdown_function')) {
            $this->trace = false;
            return;
        }
        $that =& self::$instance;
        register_shutdown_function(function () use (&$that) {
            $that->end();
        });

        if ($output) {
            $this->output_file = $output;
        } else {
            is_dir($this->output_dir) OR mkdir($this->output_dir, 0777, true);
            $this->output_file = $this->output_dir . '/output' . date('Ymd') . '.log';
            if (!is_dir($this->output_dir)) {
                $this->trace = false;
                return;
            }
        }
        if (!$cmd) {
            $this->trace = false;
            return;
        }
        $this->cmd = $cmd;
    }

    public function run()
    {
        if (!$this->trace) return;
        $ppid = getmypid();
        $this->tmpfile = $this->output_dir . "/$ppid.log";
        $command = sprintf($this->cmd, $ppid) . " >> ".$this->tmpfile." &";
        $process = proc_open($command, array(), $this->pipes);
        $var = proc_get_status($process);
        if ($spid = intval($var['pid']) + 1) {
            $this->spid = $spid;
            $t = time();
            while (!file_exists($this->tmpfile) || (filesize($this->tmpfile) <= 0)) {
                if (time() - $t > 3) break;
                usleep(1000);
            }
        }
    }

    public function end()
    {
        if (!$this->trace) return;
        if (isset($this->spid) && is_int($this->spid)) {
            proc_close(proc_open('kill -9 ' . $this->spid, array(), $this->pipes));
        }
        if (isset($this->tmpfile) && $this->tmpfile && $this->output_file) {
            shell_exec("cat " . $this->tmpfile . " >> " . $this->output_file . "; rm -f " . $this->tmpfile);
        }
    }
}