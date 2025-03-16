<?php

namespace App\Domain\Model;

use Phalcon\Mvc\Model;
use Ramsey\Uuid\Uuid;

class Visitor extends Model
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $created_at;

    /**
     * @var string
     */
    public $last_active;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('visitors');
        $this->hasMany(
            'id',
            Content::class,
            'visitor_id',
            [
                'alias' => 'contents',
                'foreignKey' => [
                    'action' => 'setNull'
                ]
            ]
        );
    }

    /**
     * Before create listener
     */
    public function beforeCreate()
    {
        $this->id = Uuid::uuid4()->toString();
        $this->created_at = date('Y-m-d H:i:s');
        $this->last_active = date('Y-m-d H:i:s');
    }

    /**
     * Before update listener
     */
    public function beforeUpdate()
    {
        $this->last_active = date('Y-m-d H:i:s');
    }
} 