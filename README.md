# API文档生成器

这是一个从PHP控制器注释生成API文档的工具库，通过解析代码中的特定注释标签，自动生成JSON格式的API文档。

## 安装
composer require apidocgenerator/apidocgenerator
## 使用方法

在你的PHP代码中引入并使用：
``` php
<?php
require 'vendor/autoload.php';

use Apidocgenerator\ApiDocGenerator;

try {
    $generator = new ApiDocGenerator(
        'test',  // 控制器目录
        'output.json'  // 输出文件路径
    );
    
    $generator->generate();
} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
}

```
## 注释格式

在控制器方法前添加如下注释：
```php
<?php

class Hello{
    /**
     * @name 测试接口
     * @desc 这是一个测试接口
     * @route /hello/index
     * @method GET
     * @param int page 页码，默认1
     * @param int pageSize 每页数量，默认10
     * @response {"code":200,"data":[{"id":1,"name":"张三"},{"id":2,"name":"李四"}]}
     */
    public function index(){
        echo "hello";
    }

    /**
     * @name 第二个接口
     * @desc 这是第二个测试接口
     * @route /hello/asds
     * @method POST
     * @param int page 页码，默认1
     * @param int pageSize 每页数量，默认10
     * @response {"code":200,"data":[{"id":1,"name":"张三"},{"id":2,"name":"李四"}]}
     */
    public function asd(){
        echo "hello";
    }
}
```
## 生成的文档结构

生成的JSON文件将包含所有解析的API信息：
``` json
[
    {
        "name": "测试接口",
        "desc": "这是一个测试接口",
        "route": "\/hello\/index",
        "method": "GET",
        "params": [
            {
                "type": "int",
                "name": "page",
                "desc": "页码，默认1"
            },
            {
                "type": "int",
                "name": "pageSize",
                "desc": "每页数量，默认10"
            }
        ],
        "response": {
            "code": 200,
            "data": [
                {
                    "id": 1,
                    "name": "张三"
                },
                {
                    "id": 2,
                    "name": "李四"
                }
            ]
        }
    },
    {
        "name": "第二个接口",
        "desc": "这是第二个测试接口",
        "route": "\/hello\/asds",
        "method": "POST",
        "params": [
            {
                "type": "int",
                "name": "page",
                "desc": "页码，默认1"
            },
            {
                "type": "int",
                "name": "pageSize",
                "desc": "每页数量，默认10"
            }
        ],
        "response": {
            "code": 200,
            "data": [
                {
                    "id": 1,
                    "name": "张三"
                },
                {
                    "id": 2,
                    "name": "李四"
                }
            ]
        }
    }
]
```