<?php

use HeadlessChromium\BrowserFactory;
use Symfony\Component\Process\Process;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class PodcastScraper
{
    const TIMEOUT_TICK = 25;

    public static $tags = [];
    public static $podcasts = [];

    protected static $process = null;

    public static function tags(): array
    {
        return static::$tags;
    }

    public static function get(bool $breakCache = false): array
    {
        $cache = new FilesystemAdapter();

        // breakcache
        if ($breakCache) {
            if ($cache->hasItem('podcasts')) {
                $cache->deleteItem('podcasts');
            }
        }

        return $cache->get('podcasts', function (ItemInterface $item) {
            $item->expiresAfter(3600 / 2);

            return static::_getFetch();
        });
    }

    protected static function _getFetch()
    {
        $js = file_get_contents(storage_path('podcast/hack_meteor.js'));

        static::initProcess();

        if (getenv('APP_ENV') === 'local') {
            $driver = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
        } else {
            $driver = 'chromium-browser';
        }

        $browserFactory = new BrowserFactory($driver);

        // starts headless chrome
        $browser = $browserFactory->createBrowser([
            // 'headless' => false, // disable headless mode
            'connectionDelay' => 0.8, // add 0.8 second of delay between each instruction sent to chrome,
            'debugLogger' => filter_var(getenv('DEBUG', false), FILTER_VALIDATE_BOOLEAN) ? 'php://stdout' : null, // will enable verbose mode
            // 'windowSize' => [1920, 1000],
            'enableImages' => false,
            // 'noSandbox' => true,
        ]);

        // creates a new page and navigate to an url
        $page = $browser->createPage();

        $page->addPreScript($js, ['onLoad' => true]);

        $navigate = $page->navigate('https://www.superdatascience.com/podcast');
        $navigate->waitForNavigation();

        static::$podcasts = static::waitingForResult($page, 'window.fetchedPodcasts');
        static::$tags = static::waitingForResult($page, 'window.fetchedTags');

        $browser->close();
        static::stopBackgroundProcess();

        static::$tags = array_combine(
            array_column(static::$tags, '_id'),
            array_column(static::$tags, 'title')
        );

        return array_map(function ($podcast) {
            // change tags[_id] into a proper name
            $podcast['tags'] = array_map(function ($tag) {
                return static::$tags[$tag] ?? null;
            // return [
                //     'id' => $tag,
                //     'title' => $tags[$tag] ?? null,
                // ];
            }, $podcast['tags']);

            return $podcast;
        }, static::$podcasts);
    }

    protected static function waitingForResult($page, string $waitingScript)
    {
        $payload = null;
        $foundPayload = false;

        $i = 0;

        // wait for payload
        while (false === $foundPayload) {
            sleep(1);

            // evaluate script in the browser
            $payload = $page->evaluate($waitingScript)->getReturnValue();

            if ($payload) {
                $foundPayload = true;
            }

            // fallback/timeout after 25 seconds.
            if (false === $foundPayload && $i >= static::TIMEOUT_TICK) {
                $foundPayload = true;
            }

            ++$i;
        }

        return $payload;
    }

    protected static function initProcess()
    {
        $driver = storage_path('podcast/bin/chromedriver-mac');

        static::$process = new Process([$driver], null, ['DISPLAY' => ($_ENV['DISPLAY'] ?? ':0')]);
        static::$process->start();

        return static::$process;
    }

    protected static function stopBackgroundProcess()
    {
        if (static::$process) {
            static::$process->stop();
            static::$process = null;
        }
    }
}
