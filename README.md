# AWS CloudWatch Logs for Laravel

[![Version](https://img.shields.io/packagist/v/kirubha7/cloudwatch-logs-laravel.svg)](https://packagist.org/packages/kirubha7/cloudwatch-logs-laravel)
[![Downloads](https://img.shields.io/packagist/dt/kirubha7/cloudwatch-logs-laravel.svg)](https://packagist.org/packages/kirubha7/cloudwatch-logs-laravel/stats)

Before using this library, it's recommended to get acquainted with the [pricing](https://aws.amazon.com/en/cloudwatch/pricing/) for AWS CloudWatch services.

Please press **&#9733; Star** button if you find this library useful.

## Disclaimer
This library uses AWS API through AWS PHP SDK, which has limits on concurrent requests. It means that on high concurrent or high load applications it may not work on it's best way. Please consider using another solution such as logging to the stdout and redirecting logs with fluentd.

## Requirements
* PHP ^7.2
* aws/aws-sdk-php": "^3.18
* AWS account with proper permissions (see list of permissions below)

## Features
* Creating Log Groups
* Creating Log Streams
* Check if Log Groups exists or not
* Check if Log Stream exists or not
* Sending Logs to cloudwatch log stream
* AWS CloudWatch Logs staff lazy loading
* Suitable for web applications and for long-living CLI daemons and workers

## Installation
Install the latest version with [Composer](https://getcomposer.org/) by running


```bash
$ composer require kirubha7/cloudwatch-logs-laravel
```

## Basic Usage
```php
<?php

use kirubha7\CloudwatchLogsLaravel\Cloudwatchlogs;
use Aws\CloudWatchLogs\CloudWatchLogsClient;

$client = [
    'region' => 'AWS_REGION',
    'version' => '2014-03-28',
    'credentials' => [
        'key' => 'AWS_KEY',
        'secret' => 'AWS_SECRET',
    ]
];
$client  = new CloudWatchLogsClient($client);//Intialize AWS Clinet
$logs = new Cloudwatchlogs($client);
try{

	//YOUR CONTEXT 
    $datas = [
        'test' => 'package',
        'version' => 1,
    ];
    $retentionDays = 30;//By Default retention days upto 14 days

    /**
      Generally putLog function returns associate array
      If Logs sends successfully => ['status' => true,'message' => 'Log sended successfully']
      If Logs failed to sends => ['status' => false,'error' => 'EXCEPTION_MESSAGE']
    */
    $res = $logs->putLog('YOUR_LOGGROUP_NAME','YOUR_LOGSTREAM_NAME',$retentionDays,$datas);

    if($res['status']){//If log sends successfully
        echo $res['message'];
    }else{//If log fails to sends
        echo $res['error'];
    }
    
}catch(\Exception $e){
    echo $e->getMessage();
}
```

# To check if Log Group exists

```php
<?php
    /**
      Generally checkLogGroupExists function returns associate array
      If Logs group exists => ['status' => true,'message' => 'Log Group Exists']
      If Logs group not exists => ['status' => false,'error' => 'EXCEPTION_MESSAGE']
    */

    $res = $logs->checkLogGroupExists('YOUR_LOG_GROUP_NAME');

    if($res['status']){//If log group is exists
        echo $res['message'];
    }else{//If log group not exists
        echo $res['error'];
    }
?>
```

# To check if Log Stream exists

```php
<?php
    /**
      Generally checkLogStreamExists function returns associate array
      If Logs stream exists => ['status' => true,'message' => 'Log Stream Exists']
      If Logs stream not exists => ['status' => false,'error' => 'EXCEPTION_MESSAGE']
    */
    
    $res = $logs->checkLogStreamExists('YOUR_LOG_STREAM_NAME');

    if($res['status']){//If log stream is exists
        echo $res['message'];
    }else{//If log stream not exists
        echo $res['error'];
    }
?>
```

# To create log group

```php
<?php
    /**
      Generally createLogGroup function returns associate array
      If Logs group created successfully => ['status' => true,'message' => 'Log Group Created Successfully']
      If Logs group fails to create  => ['status' => false,'error' => 'EXCEPTION_MESSAGE']
    */

    $res = $logs->createLogGroup('YOUR_LOG_GROUP_NAME');

    if($res['status']){//If log group created successfully
        echo $res['message'];
    }else{//If log group fails to create
        echo $res['error'];
    }
?>
```

# To craete log stream

```php
<?php
     /**
      Generally createLogStream function returns associate array
      If Logs stream created successfully => ['status' => true,'message' => 'Log Stream Created Successfully']
      If Logs stream fails to create  => ['status' => false,'error' => 'EXCEPTION_MESSAGE']
    */

    $res = $logs->createLogStream('YOUR_LOG_GROUP_NAME','YOUR_LOG_STREAM_NAME');

    if($res['status']){//If log stream created successfully
        echo $res['message'];
    }else{//If log stream fails to create
        echo $res['error'];
    }
?>
```

# AWS IAM needed permissions
if you prefer to use a separate programmatic IAM user (recommended) or want to define a policy, make sure following permissions are included:
1. `CreateLogGroup` [aws docs](https://docs.aws.amazon.com/AmazonCloudWatchLogs/latest/APIReference/API_CreateLogGroup.html)
1. `CreateLogStream` [aws docs](https://docs.aws.amazon.com/AmazonCloudWatchLogs/latest/APIReference/API_CreateLogStream.html)
1. `PutLogEvents` [aws docs](https://docs.aws.amazon.com/AmazonCloudWatchLogs/latest/APIReference/API_PutLogEvents.html)
1. `PutRetentionPolicy` [aws docs](https://docs.aws.amazon.com/AmazonCloudWatchLogs/latest/APIReference/API_PutRetentionPolicy.html)
1. `DescribeLogStreams` [aws docs](https://docs.aws.amazon.com/AmazonCloudWatchLogs/latest/APIReference/API_DescribeLogStreams.html)
1. `DescribeLogGroups` [aws docs](https://docs.aws.amazon.com/AmazonCloudWatchLogs/latest/APIReference/API_DescribeLogGroups.html)

When setting the `$createGroup` argument to `false`, permissions `DescribeLogGroups` and `CreateLogGroup` can be omitted

## AWS IAM Policy full json example
```json
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Effect": "Allow",
            "Action": [
                "logs:CreateLogGroup",
                "logs:DescribeLogGroups"
            ],
            "Resource": "*"
        },
        {
            "Effect": "Allow",
            "Action": [
                "logs:CreateLogStream",
                "logs:DescribeLogStreams",
                "logs:PutRetentionPolicy"
            ],
            "Resource": "{LOG_GROUP_ARN}"
        },
        {
            "Effect": "Allow",
            "Action": [
                "logs:PutLogEvents"
            ],
            "Resource": [
                "{LOG_STREAM_1_ARN}",
                "{LOG_STREAM_2_ARN}"
            ]
        }
    ]
}
```

## Issues
Feel free to [report any issues](https://github.com/kirubha7/cloudwatch-logs-laravel/issues/new)


___

Made in India

