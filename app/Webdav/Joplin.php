<?php

namespace App\Webdav;

use PDO;
use Utils\DB;
use function extension_loaded;
use Sabre\DAV\Server as DAVServer;
use Sabre\DAV\Auth\Plugin as AuthPlugin;
use Sabre\DAV\Sync\Plugin as SyncPlugin;
use Sabre\DAV\Locks\Plugin as LocksPlugin;
use Sabre\DAV\FS\Directory as DAVDirectory;
use Sabre\DAV\Browser\Plugin as BrowserPlugin;
use Sabre\DAV\Locks\Backend\File as BackendFile;

class Joplin
{
    const REALM = 'D$FfTFsh23*H4c#PPK?*%mX$FaVRF7$&mA9z$bajfDBpgW8JfPezVcad3&XhzTAh';

    public function index()
    {
        // Now we're creating a whole bunch of objects
        $rootDirectory = new DAVDirectory(storage_path('webdav/storage'));

        // The server object is responsible for making sense out of the WebDAV protocol
        $server = new DAVServer($rootDirectory);

        // If your server is not on your webroot, make sure the following line has the
        // correct information
        $server->setBaseUri('/webdav');

        $this->pluginAuth($server);
        $this->pluginLocks($server);
        // This ensures that we get a pretty index in the browser, but it is
        // optional.
        $server->addPlugin(new BrowserPlugin());
        $server->addPlugin(new SyncPlugin());

        // All we need to do now, is to fire up the server
        return $server->exec();
    }

    /**
     * Register plugin for Authorization backend.
     */
    protected function pluginAuth(DAVServer $server): void
    {
        $db = DB::connect([
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => 3306,
            'database' => getenv('DB_WEBDEV'),
            'username' => getenv('DB_WEBDEV_USER'),
            'password' => getenv('DB_WEBDEV_PASSWORD'),
            'unix_socket' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => getenv('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ])->getConnection();

        $authBackend = new Auth\BasicPDO($db->getPdo());
        // We're assuming that the realm name is called 'SabreDAV'.
        $authBackend->setRealm(static::REALM);
        // Creating the plugin.
        $authPlugin = new AuthPlugin($authBackend);

        // adding the plugin to the webdav server
        $server->addPlugin($authPlugin);
    }

    /**
     * The lock manager is reponsible for making sure users don't overwrite
     * each others changes.
     */
    protected function pluginLocks(DAVServer &$server): void
    {
        $lockBackend = new BackendFile(storage_path('webdav/locks.lck'));
        $lockPlugin = new LocksPlugin($lockBackend);

        $server->addPlugin($lockPlugin);
    }
}
