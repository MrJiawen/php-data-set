<?php

namespace Jw\DataSet\DataSet;

/**
 * YAML 操作数据集
 * Class YAMLDataSet
 * @package Jw\Support\YAMLSeed
 */
class YAMLDataSet
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
    public $ymlFileCollection = [];

    public function __construct(string $path = null)
    {
        $this->path = $path;

        if ($this->path) {
            $this->initFileCollection($this->path);
        }
    }

    /**
     * 读取目录下所有的 yml 文件
     * @param $dir
     * @return array
     * @Author jiaWen.chen
     */
    private function initFileCollection($dir)
    {
        if (is_dir($dir)) {
            $handle = opendir($dir);
            while (($file = readdir($handle)) !== false) {
                if ($file != '.' && $file != "..") {
                    if (is_dir($dir . "/" . $file)) {
                        $files[$file] = $this->initFileCollection($dir . "/" . $file);
                    } else if(explode('.',$file)[1] === 'yml') {
                        $this->ymlFileCollection[] = $dir . "/" . $file;
                    }
                }
            }
        }
    }
}