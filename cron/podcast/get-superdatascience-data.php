<?php

use HeadlessChromium\BrowserFactory;
use Symfony\Component\Process\Process;

require_once __DIR__.'/../../vendor/autoload.php';
// https://github.com/radekmie/MiniMongoExplorer/blob/master/extension/lib/inject.js

$js = file_get_contents(storage_path('podcast/hack_meteor.js'));

$driver = storage_path('podcast/bin/chromedriver-mac');
$chromeProcess = new Process([$driver], null, ['DISPLAY' => ($_ENV['DISPLAY'] ?? ':0')]);
$chromeProcess->start();
// print('Binary: '. $driver ."\n\n");
// dump($chromeProcess);
// $chromeProcess->stop();
// return;

$driver = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
$browserFactory = new BrowserFactory($driver);
// starts headless chrome
$browser = $browserFactory->createBrowser([
    // 'headless' => false, // disable headless mode
    'connectionDelay' => 0.8, // add 0.8 second of delay between each instruction sent to chrome,
    // 'debugLogger' => 'php://stdout', // will enable verbose mode
    // 'windowSize' => [1920, 1000],
    'enableImages' => false,
    // 'noSandbox' => true,
]);

// creates a new page and navigate to an url
$page = $browser->createPage();

$page->addPreScript($js, ['onLoad' => true]);

$navigate = $page->navigate('https://www.superdatascience.com/podcast');
$navigate->waitForNavigation();

function waitingForResult($page, string $waitingScript)
{
	$payload = null;
	$foundPayload = false;
	// wait
	while($foundPayload === false) {
		print('waiting for "'.$waitingScript.'"...'."\n");
		\sleep(1);

		// evaluate script in the browser
		$payload = $page->evaluate($waitingScript)->getReturnValue();

		var_dump($payload);

		if ($payload) {
			$foundPayload = true;
			print('Found it!!'."\n");
		}
	}

	return $payload;
}

$podcasts = waitingForResult($page, 'window.fetchedPodcasts');
$tags = waitingForResult($page, 'window.fetchedTags');

$browser->close();
$chromeProcess->stop();

var_dump(count($podcasts), $tags);

die;
sleep(10);

