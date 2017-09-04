<?php

error_reporting(-1);

require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

Yii::setAlias('@yiiunit/oss', __DIR__);
Yii::setAlias('@hxy2015/oss', dirname(__DIR__));