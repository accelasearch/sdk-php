<?php
namespace AccelaSearch\ProductMapper;

class Collector {
    private $host_name;
    private $database_name;
    private $username;
    private $password;

    public function __construct(
        string $host_name,
        string $database_name,
        string $username,
        string $password
    ) {
        $this->host_name = $host_name;
        $this->database_name = $database_name;
        $this->username = $username;
        $this->password = $password;
    }

    public function getHostName(): string {
        return $this->host_name;
    }

    public function getDatabaseName(): string {
        return $this->database_name;
    }

    public function getUsername(): string {
        return $this->username;
    }

    public function getPassword(): string {
        return $this->password;
    }
}