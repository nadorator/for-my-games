<?php

namespace App\Domain\Model;

use Phalcon\Mvc\Model;
use Ramsey\Uuid\Uuid;

class Content extends Model
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $game_id;

    /**
     * @var string|null
     */
    public $visitor_id;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $body;

    /**
     * @var string
     */
    public $status;

    /**
     * @var string
     */
    public $created_at;

    /**
     * @var string|null
     */
    public $updated_at;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('contents');
        $this->belongsTo(
            'game_id',
            Game::class,
            'id',
            [
                'alias' => 'game',
                'foreignKey' => [
                    'message' => 'Game does not exist'
                ]
            ]
        );
        $this->belongsTo(
            'visitor_id',
            Visitor::class,
            'id',
            [
                'alias' => 'visitor',
                'foreignKey' => [
                    'allowNulls' => true
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
        $this->status = 'draft';
    }

    /**
     * Before update listener
     */
    public function beforeUpdate()
    {
        $this->updated_at = date('Y-m-d H:i:s');
    }
} 