<?php

namespace Jw\DataSet\Command;

use Illuminate\Console\Command;
use Jw\DataSet\Event\OutputEvent;

class DataSetOutputCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'yml:output 
    {--d|connection=mysql : 选择连接的数据库配置 } 
    {--t|table= : 操作相应的表, 多个表用逗号"，" 间隔 ,如果不选择默认是所有的表}
    {--p|output_path=database/data_set : 输出的位置 }
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '数据集的导出操作 （从yml文件导入到数据库）';

    /**
     * 命令行输入的选项的值
     * @var array
     */
    protected $options = [
        'connection' => null,
        'table' => null,
        'output_path' => null,
    ];

    /**
     * DataSetInputCommand constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @Author jiaWen.chen
     */
    public function handle()
    {
        // 1. 初始化选项
        $this->initOptions();

        $this->options['table'] = empty($this->options['table']) ? null : explode(',', $this->options['table']);

        (new OutputEvent(
            base_path($this->options['output_path']),
            $this->options['table'],
            $this->options['connection'],
            $this
        ))->handle();
    }

    /**
     * 初始化 命令行输入的选项值
     * @Author jiaWen.chen
     */
    private function initOptions()
    {
        $this->options = [
            'connection' => $this->option('connection'),
            'table' => $this->option('table'),
            'output_path' => $this->option('output_path'),
        ];
    }
}