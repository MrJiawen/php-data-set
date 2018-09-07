<?php

namespace Jw\DataSet\Event;

use Illuminate\Console\Command;
use Jw\DataSet\Exceptions\InvalidArgumentException;
use Jw\Support\Tool\FileTool;
use Symfony\Component\Yaml\Yaml;

/**
 * YML 操作数据集
 * Class YAMLDataSet
 * @package Jw\Support\YAMLSeed
 */
class OutputEvent
{
    /**
     * 操作路径
     * @var null|string
     */
    public $path = null;

    /**
     * 文件集合
     * @var array
     */
    public $tables = [];

    /**
     * 数据库连接 支付从
     * @var null|string
     */
    public $connection = null;      // 数据库连接对象
    public $connectionStr = '';     // 数据库连接配置

    protected $artisan = null;

    /**
     * 所有的yml数据
     * @var array
     */
    public $ymlData = [];


    /**
     * OutputEvent constructor.
     * @param string $outputPatch
     * @param array|null $tables
     * @param string $connection
     * @param Command|null $artisan
     */
    public function __construct(string $outputPatch, Array $tables = null, string $connection = 'mysql', Command $artisan = null)
    {
        $this->path = $outputPatch;
        $this->tables = $tables;
        $this->connectionStr = $connection;
        $this->artisan = $artisan;

        $this->initMemberVariable();
    }

    /**
     * 成员变量的初始化
     * @Author jiaWen.chen
     */
    private function initMemberVariable()
    {
        // 1. 建立数据库连接
        $this->connection = \DB::connection($this->connectionStr);

        // 2 . 首先读取yaml 中的数据
        $this->getYmlData();
    }

    /**
     * 获取对应的yaml 数据
     * @Author jiaWen.chen
     */
    private function getYmlData()
    {
        $files = FileTool::getAllFiles($this->path, '.yml');

        // 1.获取相应的yml 数据，如果没有table 限制，则获取所有的yml数据
        array_map(function ($item) {
            $itemData = Yaml::parseFile($item);
            if (empty($itemData)) {
                return false;
            }
            if (is_array($this->tables) && !in_array(array_keys($itemData)[0], $this->tables)) {
                return false;
            }

            // 1.1. 填充相应的yml数据
            $this->ymlData[] = [
                'tableName' => array_keys($itemData)[0],
                'tableRecord' => $itemData[array_keys($itemData)[0]]
            ];

            // 1.2 检查对应的表是否存在
            $result = $this->connection->table('information_schema.TABLES')
                ->where([
                    'TABLE_SCHEMA' => config('database.connections.' . $this->connectionStr . '.database'),
                    'TABLE_NAME' => array_keys($itemData)[0]
                ])->first();
            if (empty($result)) {
                $this->error('数据库中发现' . array_keys($itemData)[0] . '表不存在');
            }

            $this->printOut("获取yml 数据，table 名称：" . array_keys($itemData)[0] . "，共计" . count($itemData[array_keys($itemData)[0]]) . " 条记录");
            return true;
        }, $files);

        // 2. 如果有table 限制， 则进行校验数据是否全部获取
        $ymlData = array_map(function ($item) {
            return $item['tableName'];
        }, $this->ymlData);
        if (is_array($this->tables) && !empty($diff = array_diff($this->tables, $ymlData))) {
            $this->error("在table 参数中 发现，找不到" . implode(',', $diff) . " 这" . count($diff) . "个数据表的 yml 数据集");
        }
    }

    /**
     * 开始数据清空
     * @param bool $truncate
     * @return $this
     * @Author jiaWen.chen
     */
    public function handle(bool $truncate = true)
    {
        array_map(function ($item) use ($truncate) {
            // 1. 清空表
            if ($truncate) {
                $this->connection->statement('truncate TABLE ' . $item['tableName']);
            }
            $result = $this->connection->table($item['tableName'])->insert($item['tableRecord']);
            if ($result) {
                $this->printOut('数据表填充成功,表名' . $item['tableName'] . '为,共计' . count($item['tableRecord']) . '条记录');
            } else {
                $this->error('数据表填充失败,表名' . $item['tableName']);
            }
        }, $this->ymlData);

        return $this;
    }

    /**
     * 如果是命令行则可以输出日志
     * @param $message
     * @Author jiaWen.chen
     */
    public function printOut($message)
    {
        if ($this->artisan) {
            $this->artisan->info($message);
        }
    }

    /**
     * 如果数据异常则停止运行
     * @param $message
     * @throws InvalidArgumentException
     * @Author jiaWen.chen
     */
    public function error($message)
    {
        if ($this->artisan) {
            $this->artisan->error($message);
            exit();
        } else {
            throw new InvalidArgumentException($message);
        }
    }
}