
# An Oss client of yii2 extension.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist hxy2015/yii2-oss
```

or add

```json
"hxy2015/yii2-oss": "~1.0"
```

to the require section of your composer.json.

Configuration
-------------

To use this extension, you have to configure the Connection class in your application configuration:

```php
return [
    //....
    'components' => [
        'oss' => [
            'hostname' => 'localhost',
            'bucket' => 'risk-test',
            'accessId' => 'test', // oss id
            'accessKey' => 'test', // oss id
        ],
    ]
];
```

Usage
-------------

Upload File

```php
$oss = \Yii::$app->get('oss');
$oss->putObjectByContent('some_dir/some_file_name', 'hehe');

$filename = 'test.txt';
file_put_contents($filename, 'hehe');
$oss->putObjectByFile('some_dir/some_file_name', $filename);
```

Download File

```php
$oss = \Yii::$app->get('oss');
$oss->getObjectContent('some_dir/some_file_name');

$filename = 'test.txt';
$oss->getObjectFile('some_dir/some_file_name', $filename);
```

Test File Exist

```php
$oss = \Yii::$app->get('oss');
$oss->isObjectExist('some_dir/some_file_name');
```

