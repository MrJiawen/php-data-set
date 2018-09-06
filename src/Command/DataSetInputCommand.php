<?php

namespace Jw\DataSet\Command;

use Illuminate\Console\Command;
use Illuminate\Database\MySqlConnection;
use Illuminate\Support\Collection;
use Jw\Support\Tool\FileTool;
use Symfony\Component\Yaml\Yaml;

class DataSetInputCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'yml:input 
    {--d|connection=mysql : 选择连接的数据库配置 } 
    {--t|table= : 操作相应的表, 多个表用逗号"，" 间隔 }
    {--i|id= : 操作相应的id，可以是字符串用逗号拼接(1,2,3,4,5,6)，可以是区间，使用 - 拼接(1-10)，注意如果你的id是 uuid 则不要使用 区间}
    {--p|input_path=database/data_set : 输出的位置 }
    {--a|append : 生成的内容以追加的形式,存放到对应的输出文件 }
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '数据集的导入操作 （从数据库导入到yml文件）';

    /**
     * 命令行输入的选项的值
     * @var array
     */
    protected $options = [
        'connection' => null,
        'table' => null,
        'id' => null,
        'input_path' => null,
        'append' => false,
    ];
    /**
     * 数据库连接
     * @var
     */
    protected $connection;
    /**
     * 配置文件的值
     * @var
     */
    protected $config;
    /**
     * 执行一个表的数据迁移所需要的数据
     * @var array
     */
    protected $oneHandel = [
        'tableName' => null,
        'primaryKey' => null,
        'primaryValue' => [],
        'append' => null,
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
        // 2. 操作对应的表
        array_map(function ($item) {
            $this->oneHandel = [
                'tableName' => $item,
                'primaryKey' => null,
                'primaryValue' => $this->options['id'],
                'append' => $this->options['append'],
            ];
            // 3. 检查对应的参数
            $this->checkOneHandel();
            // 4. 生成一条yml 记录
            $this->generateOne();
        }, explode(',', $this->options['table']));
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
            'id' => $this->option('id'),
            'input_path' => $this->option('input_path'),
            'append' => $this->option('append'),
        ];
        if (!$this->options['table']) {
            $this->options['table'] = $this->ask('请输入需要迁移出数据数据的数据表名?');
        }
        if (!$this->options['id']) {
            $this->options['id'] = $this->ask('请输入迁移出数据的id号(多个用逗号","间隔，如果是一个区间则使用"-"间隔)?');
        }
    }

    /**
     * 检查一个表数据生成时 所需要的配置项
     * @Author jiaWen.chen
     */
    public function checkOneHandel()
    {
        // 1. 数据库连接
        if (!($this->connection instanceof MySqlConnection)) {
            $this->connection = \DB::connection($this->options['connection']);
        }

        // 2. 检查数据表
        $result = $this->connection->table('information_schema.TABLES')
            ->where([
                'TABLE_SCHEMA' => config('database.connections.' . $this->options['connection'] . '.database'),
                'TABLE_NAME' => $this->oneHandel['tableName']
            ])->first();
        if (empty($result)) {
            $this->error($this->oneHandel['tableName'] . '表不存在');
            exit();
        }

        // 3. 获取对表的主键
        $fields = $this->connection->select('show full columns from ' . $this->oneHandel['tableName']);
        array_map(function ($item) {
            if ($item->Key == 'PRI') {
                $this->oneHandel['primaryKey'] = $item->Field;
            }
        }, $fields);

        // 4. 把不同格式的id整理为数组
        $this->oneHandel['primaryValue'] = explode(',', $this->oneHandel['primaryValue']);
        $response = [];
        array_map(function ($item) use (&$response) {
            if (strpos($item, '-') != 0) {
                $between = explode('-', $item);
                if ($between[0] > $between[1] || strlen($between[0]) > 15 || strlen($between[1]) > 15) {
                    $this->error("id输入有异常,不能逆序，只能正序；也许id是uuid，但它是不能使用区间的，程序停止运行");
                    exit();
                }
                for ($i = $between[0]; $i <= $between[1]; $i++) {
                    $response[] = $i;
                }
            } else {
                $response[] = $item;
            }
        }, $this->oneHandel['primaryValue']);
        $this->oneHandel['primaryValue'] = array_unique($response);
    }

    /**
     * 生成一个表的数据
     * @Author jiaWen.chen
     */
    public function generateOne()
    {
        //1. 获取数据库内容
        $models = $this->connection->table($this->oneHandel['tableName'])
            ->whereIn($this->oneHandel['primaryKey'], $this->oneHandel['primaryValue'])->get()->toArray();

        $fileName = base_path($this->options['input_path']) . '/' . $this->oneHandel['tableName'] . '.yml';

        // 2. 如果没有这个文件，则追加写自动改为覆盖写
        if (!FileTool::exists($fileName)) {
            $this->oneHandel['append'] = false;
        }

        // 3. 判断是否为追加写
        if ($this->oneHandel['append']) {
            $ymlData = Yaml::parseFile($fileName);
            $ymlData[$this->oneHandel['tableName']] = array_merge(
                json_decode(json_encode($models), true),
                $ymlData[$this->oneHandel['tableName']]
            );
            // 进行去重
            $ymlData[$this->oneHandel['tableName']] = (new Collection($ymlData[$this->oneHandel['tableName']]))->unique('id')->toArray();

            $ymlData = Yaml::dump($ymlData, 3, 2);
            FileTool::put($fileName, $ymlData);
            $this->info('yml 数据集合生成成功(追加)，表名为' . $this->oneHandel['tableName']);
        } else {
            $ymlData = json_decode(json_encode([$this->oneHandel['tableName'] => $models]), true);
            $ymlData = Yaml::dump($ymlData, 3, 2);

            FileTool::put($fileName, $ymlData);
            $this->info('yml 数据集合生成成功(覆盖)，表名为' . $this->oneHandel['tableName']);
        }

    }
}
