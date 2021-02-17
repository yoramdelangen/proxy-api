<?php

namespace App\Webdav\Auth;

use Sabre\DAV\Auth\Backend\AbstractBasic;

/**
 * This is an authentication backend that uses a database to manage passwords.
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class BasicPDO extends AbstractBasic
{
    /**
     * PDO table name we'll be using.
     *
     * @var string
     */
    public $tableName = 'users';

    /**
     * Reference to PDO connection.
     *
     * @var PDO
     */
    protected $pdo;

    /**
     * Creates the backend object.
     *
     * If the filename argument is passed in, it will parse out the specified file fist.
     */
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Validates a username and password.
     *
     * This method should return true or false depending on if login
     * succeeded.
     *
     * @param string $username
     * @param string $password
     *
     * @return bool
     */
    public function validateUserPass($username, $password)
    {
        $digest = md5($username.':'.$this->realm.':'.$password);

        $stmt = $this->pdo->prepare('SELECT id FROM '.$this->tableName.' WHERE username = ? AND digesta1 = ?');
        $stmt->execute([$username, $digest]);

        return (bool) $stmt->fetchColumn();
    }
}
