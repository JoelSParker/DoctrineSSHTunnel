<?php

/*
 * Author Thomas Beauchataud
 * Since 06/06/2022
 */

namespace TBCD\Doctrine\SSHTunnel;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\ParameterType;

class MySQLSSHTunnelConnection implements Connection
{

    /**
     * @var Connection
     */
    private Connection $wrappedConnection;

    /**
     * @var SSHTunnelManager
     */
    private SSHTunnelManager $sshTunnelManager;

    /**
     * @param Connection $wrappedConnection
     * @param SSHTunnelManager $sshTunnelManager
     */
    public function __construct(Connection $wrappedConnection, SSHTunnelManager $sshTunnelManager)
    {
        $this->wrappedConnection = $wrappedConnection;
        $this->sshTunnelManager = $sshTunnelManager;
    }


    /**
     * @inheritDoc
     */
    public function prepare(string $sql): Statement
    {
        return new MySQLSSHTunnelStatement($this->wrappedConnection->prepare($sql), $this->sshTunnelManager);
    }

    /**
     * @inheritDoc
     */
    public function query(string $sql): Result
    {
        $this->sshTunnelManager->open();
        $response = $this->wrappedConnection->query($sql);
        $this->sshTunnelManager->close();
        return $response;
    }

    /**
     * @inheritDoc
     */
    public function quote($value, $type = ParameterType::STRING): mixed
    {
        return $this->wrappedConnection->quote($value, $type);
    }

    /**
     * @inheritDoc
     */
    public function exec(string $sql): int
    {
        $this->sshTunnelManager->open();
        $response = $this->wrappedConnection->exec($sql);
        $this->sshTunnelManager->close();
        return $response;
    }

    /**
     * @inheritDoc
     */
    public function lastInsertId($name = null): string|int|false
    {
        $this->sshTunnelManager->open();
        $response = $this->wrappedConnection->lastInsertId($name);
        $this->sshTunnelManager->close();
        return $response;
    }

    /**
     * @inheritDoc
     */
    public function beginTransaction()
    {
        return $this->beginTransaction();
    }

    /**
     * @inheritDoc
     */
    public function commit(): void
    {
        $this->sshTunnelManager->open();
        $this->wrappedConnection->commit();
        $this->sshTunnelManager->close();
    }

    /**
     * @inheritDoc
     */
    public function rollBack(): void
    {
        $this->sshTunnelManager->open();
        $this->wrappedConnection->rollBack();
        $this->sshTunnelManager->close();
    }

    /**
     * @inheritDoc
     */
    public function getNativeConnection(): object
    {
        return $this->wrappedConnection->getNativeConnection();
    }
}