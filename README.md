# php-date-set
> 数据填充是项目必填的一个环节，特别是对于产品型 项目。

1. 如果是多人协作开发，数据填充会节约大量的时间成本；
2. 他是一个yml 格式的数据集。 这样 数据填充就与语言无关，他只需要和 数据库紧密连接即可。
3. 数据集是非常有用的 特别是做单元测试的时候 ，他是必备的。
4. 当然他可以大大的提高项目的交付性；

### 概述：
1. 本包依赖于 laravel 
2. 本包依赖于 个人的 底层包 https://github.com/MrJiawen/php-support
###  具体使用方式 ：
1.  数据库的数据到成对应的yml 文件：
    ```php
    php artisan yml:input  数据集的导入操作 （从数据库导入到yml文件）
    ```
    查看对应的参数：
    ````php
    php artisan yml:input -h
    Description:
      数据集的导入操作 （从数据库导入到yml文件）
    
    Usage:
      yml:input [options]
    
    Options:
      -d, --connection[=CONNECTION]  选择连接的数据库配置 [default: "mysql"]
      -t, --table[=TABLE]            操作相应的表, 多个表用逗号"，" 间隔
      -i, --id[=ID]                  操作相应的id，可以是字符串用逗号拼接(1,2,3,4,5,6)，可以是区间，使用 - 拼接(1-10)，注意如果你的id是 uuid 则不要使用 区间
      -p, --input_path[=INPUT_PATH]  输出的位置 [default: "database/data_set"]
      -a, --append                   生成的内容以追加的形式,存放到对应的输出文件
   ```
    
   * 如果不给任何参数 将会自动 提醒输入 相应的 table 和 主键 值
   * 默认的输出路径在 `database\data_set`
   * 默认一个数据库是一个目录。不能跨库操作

2. 把yml文件到入到数据库
    ```php
    php artisan yml:output  数据集的导出操作 （从yml文件导入到数据库）
    ```
    查看对应的参数
    ```php
    php artisan yml:output -h
    Description:
      数据集的导出操作 （从yml文件导入到数据库）
    
    Usage:
      yml:output [options]
    
    Options:
      -d, --connection[=CONNECTION]    选择连接的数据库配置 [default: "mysql"]
      -t, --table[=TABLE]              操作相应的表, 多个表用逗号"，" 间隔 ,如果不选择默认是所有的表
      -p, --output_path[=OUTPUT_PATH]  输出的位置 [default: "database/data_set"]
    ```
    * 如果不给table 参数，默认是把 所有的yml文件导入到数据库


