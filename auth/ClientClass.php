<?php
// dashboard/ClientClass.php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class Client {
    public int $id;
    public function __construct(
        public string $name,
        public string $email,
        public string $password
    ) {}
}

function addClient(Client $client) {
    if (!isset($_SESSION['listClients'])) {
        $_SESSION['listClients'] = [];
    }

    foreach ($_SESSION['listClients'] as $findClient) {
        if ($findClient->email === $client->email) {
            return "Erro: Cliente com mesmo e-mail já cadastrado!";
        }
    }

    $lastClient = end($_SESSION['listClients']);
    $client->id = ($lastClient === false) ? 1 : $lastClient->id + 1;
    
    $_SESSION['listClients'][] = $client;
    return true;
}