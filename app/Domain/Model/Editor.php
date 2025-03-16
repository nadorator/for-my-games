<?php

namespace App\Domain\Model;

use Phalcon\Mvc\Model;
use Ramsey\Uuid\Uuid;

class Editor extends Model
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $role;

    /**
     * @var string
     */
    public $created_at;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('editors');
    }

    /**
     * Before create listener
     */
    public function beforeCreate()
    {
        $this->id = Uuid::uuid4()->toString();
        $this->created_at = date('Y-m-d H:i:s');
        
        // Hash password if it hasn't been hashed already
        if (!$this->isPasswordHashed()) {
            $this->password = password_hash($this->password, PASSWORD_BCRYPT);
        }
    }

    /**
     * Check if password is already hashed
     */
    private function isPasswordHashed(): bool
    {
        return password_get_info($this->password)['algo'] !== 0;
    }

    /**
     * Validate credentials
     */
    public function validateCredentials(string $password): bool
    {
        return password_verify($password, $this->password);
    }
} 