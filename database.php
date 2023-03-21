<?php

class TokenDatabase
{

    private $connection = null;
    private $serverInfo = 'mysql:host=localhost;dbname=gmailapi';
    private $userName = 'root';
    private $passWord = '';

    public function __construct()
    {
        try {
            $this->connection = new PDO($this->serverInfo, $this->userName, $this->passWord);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    }


    public function isTokenEmpty()
    {
        $sth = $this->connection->prepare("SELECT token FROM tokens");
        $sth->execute();
        $result = $sth->rowCount();
        if ($result > 0) {
            return false;
        } else {

            return true;
        }
    }


    // Update a row/s in a Database Table
    public function UpdateToken($token)
    {
        if ($this->isTokenEmpty()) {
            $this->connection->query("INSERT INTO tokens (provider,token) VALUES ('google','$token')");
        } else {
            $this->connection->query("UPDATE tokens SET token = '$token' WHERE provider = 'google'");
        }
    }

    // Select a row/s in a Database Table
    public function getTokenFromDB()
    {
        $sql = $this->connection->prepare("SELECT token FROM tokens WHERE provider='google'");
        $sql->execute();
        $result = $sql->fetch(PDO::FETCH_ASSOC);
        return json_decode($result['token']);
    }

    public function refreshTokenInfo()
    {
        $result = $this->getTokenFromDB();
        return $result->refresh_token;
    }

}
