<?php

namespace TBCD\Doctrine\SSHTunnel;

use Symfony\Component\Process\Process;

class SSHTunnelManager
{

    /**
     * @var Process|null
     */
    private ?Process $runningProcess = null;

    /**
     * @var string
     */
    private string $sshHost;

    /**
     * @var string
     */
    private string $sshUser;

    /**
     * @var string
     */
    private string $portForwarding;

    /**
     * @var string
     */
    private string $databaseHost;

    /**
     * @var string
     */
    private string $databasePort;

    /**
     * @param string $sshHost
     * @param string $sshUser
     * @param string $portForwarding
     * @param string $databaseHost
     * @param string $databasePort
     */
    public function __construct(string $sshHost, string $sshUser, string $portForwarding, string $databaseHost, string $databasePort)
    {
        $this->sshHost = $sshHost;
        $this->sshUser = $sshUser;
        $this->portForwarding = $portForwarding;
        $this->databaseHost = $databaseHost;
        $this->databasePort = $databasePort;
    }


    /**
     * @return void
     */
    public function open(): void
    {
        $cmd = sprintf("ssh -fNg -L %s:%s:%s %s@%s", $this->portForwarding, $this->databaseHost, $this->databasePort, $this->sshUser, $this->sshHost);
        $process = new Process([$cmd]);
        $process->run();
        $this->runningProcess = $process;
    }

    /**
     * @return void
     */
    public function close(): void
    {
        if ($this->runningProcess) {
            $this->runningProcess->stop();
            $this->runningProcess = null;
        }
    }

}