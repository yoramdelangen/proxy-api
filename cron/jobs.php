<?php

use Cron\Cron;
use Cron\Job\ShellJob;
use Cron\Executor\Executor;
use Cron\Resolver\ArrayResolver;
use Cron\Schedule\CrontabSchedule;

require_once __DIR__.'/../vendor/autoload.php';

$cronjobs =  array_filter([
	['command' => '', 'schedule' => ''],
]);

$resolver = new ArrayResolver();
foreach ($cronjobs as $cron) {
	$job = new ShellJob();
	$job->setCommand($cron['command']);
	$job->setSchedule(new CrontabSchedule($cron['schedule']));

	$resolver->addJob($job);
}

$cron = new Cron();
$cron->setExecutor(new Executor());
$cron->setResolver($resolver);

$cron->run();