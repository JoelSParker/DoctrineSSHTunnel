<?php

/*
 * Author Thomas Beauchataud
 * Since 07/06/2022
 */

namespace TBCD\Doctrine\SSHTunnel;

use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\ParameterType;

class MySQLSSHTunnelStatement implements Statement
{

    /**
     * @var Statement
     */
    private Statement $wrappedStatement;

    /**
     * @var SSHTunnelManager
     */
    private SSHTunnelManager $sshTunnelManager;

    /**
     * @param Statement $wrappedStatement
     * @param SSHTunnelManager $sshTunnelManager
     */
    public function __construct(Statement $wrappedStatement, SSHTunnelManager $sshTunnelManager)
    {
        $this->wrappedStatement = $wrappedStatement;
        $this->sshTunnelManager = $sshTunnelManager;
    }


    /**
     * @inheritDoc
     */
    public function bindValue($param, $value, $type = ParameterType::STRING): bool
    {
        return $this->wrappedStatement->bindValue($param, $value, $type);
    }

    /**
     * @inheritDoc
     */
    public function bindParam($param, &$variable, $type = ParameterType::STRING, $length = null): bool
    {
        return $this->wrappedStatement->bindParam($param, $variable, $type, $length);
    }

    /**
     * @inheritDoc
     */
    public function execute($params = null): Result
    {
        $this->sshTunnelManager->open();
        $response = $this->wrappedStatement->execute($params);
        $this->sshTunnelManager->close();
        return $response;
    }
}