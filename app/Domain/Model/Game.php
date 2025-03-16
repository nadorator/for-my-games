<?php

namespace App\Domain\Model;

use Phalcon\Mvc\Model;
use Ramsey\Uuid\Uuid;

class Game extends Model
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string|null
     */
    public $description;

    /**
     * @var string
     */
    public $created_at;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('games');
        $this->hasMany(
            'id',
            Content::class,
            'game_id',
            [
                'alias' => 'contents',
                'foreignKey' => [
                    'action' => 'cascade'
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
    }
} 