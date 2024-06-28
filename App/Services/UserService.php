<?php
namespace App\Services;

use App\Models\User;
use App\Database\SqliteDatabase;

class UserService
{
    private SqliteDatabase $database;

    public function __construct(SqliteDatabase $database)
    {
        $this->database = $database;
    }

    public function createUser(string $username, string $password): void
    {
        $hashedPassword = md5($password);
        $newUser = new User($username, $hashedPassword);
        $this->database->saveUser($newUser);
        echo "User registered successfully with ID: " . $newUser->getId() . ".\n";
    }

    public function login(string $username, string $password): ?User
    {
            $userData = $this->database->getUserByUsernameAndPassword($username, $password);

            return new User(
                $userData['username'],
                $userData['password'],
                $userData['id']
            );
    }
}