<?php

/*
 * Author Thomas Beauchataud
 * Since 06/06/2022
 */

namespace TBCD\Doctrine\SSHTunnel;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\AbstractMySQLDriver;
use Doctrine\DBAL\Driver\Connection as ConnectionInterface;
use Doctrine\DBAL\Driver\PDO\Exception;
use PDO;
use PDOException;

class MySQLSSHTunnelDriver extends AbstractMySQLDriver
{

    /**
     * @inheritDoc
     */
    public function connect(array $params): ConnectionInterface
    {
        $driverOptions = $params['driverOptions'] ?? [];

        if (!empty($params['persistent'])) {
            $driverOptions[PDO::ATTR_PERSISTENT] = true;
        }

        $sshTunnelManager = new SSHTunnelManager($params['driverOptions']['tunnel_host'], $params['driverOptions']['tunnel_user'], $params['driverOptions']['tunnel_port'], $params['host'], $params['port']);

        try {
            $sshTunnelManager->open();
            $pdo = new PDO($this->constructPdoDsn($params), $params['user'] ?? '', $params['password'] ?? '', $driverOptions);
            $sshTunnelManager->close();
        } catch (PDOException $exception) {
            throw Exception::new($exception);
        }

        return new MySQLSSHTunnelConnection(new Driver\PDO\Connection($pdo), $sshTunnelManager);
    }

    /**
     * Constructs the MySQL PDO DSN.
     *
     * @param array $params
     * @return string
     */
    private function constructPdoDsn(array $params): string
    {
        $dsn = 'mysql:';
        if (isset($params['host']) && $params['host'] !== '') {
            $dsn .= 'host=' . $params['host'] . ';';
        }

        if (isset($params['driverOptions']['tunnel_port'])) {
            $dsn .= 'port=' . $params['driverOptions']['tunnel_port'] . ';';
        }

        if (isset($params['dbname'])) {
            $dsn .= 'dbname=' . $params['dbname'] . ';';
        }

        if (isset($params['unix_socket'])) {
            $dsn .= 'unix_socket=' . $params['unix_socket'] . ';';
        }

        if (isset($params['charset'])) {
            $dsn .= 'charset=' . $params['charset'] . ';';
        }

        return $dsn;
    }
}