<?php

namespace App\Services\User;

use App\Domain\Model\Visitor;
use Phalcon\Mvc\Micro;

class VisitorController
{
    /**
     * @var Micro
     */
    private $app;

    /**
     * Constructor
     */
    public function __construct(Micro $app)
    {
        $this->app = $app;
    }

    /**
     * Create a new visitor
     * POST /visitors
     */
    public function createAction()
    {
        try {
            $visitor = new Visitor();
            if ($visitor->save() === false) {
                return $this->sendErrorResponse($visitor->getMessages());
            }

            return $this->app->response->setJsonContent([
                'id' => $visitor->id,
                'created_at' => $visitor->created_at
            ])->setStatusCode(201);
        } catch (\Exception $e) {
            return $this->sendErrorResponse('Error creating visitor: ' . $e->getMessage());
        }
    }

    /**
     * Get a visitor
     * GET /visitors/{visitor_id}
     * 
     * @param string $visitor_id Visitor ID
     */
    public function getAction(string $visitor_id)
    {
        try {
            $visitor = Visitor::findFirst([
                'conditions' => 'id = :id:',
                'bind' => [
                    'id' => $visitor_id
                ]
            ]);

            if (!$visitor) {
                return $this->sendErrorResponse('Visitor not found', 404);
            }

            return $this->app->response->setJsonContent([
                'id' => $visitor->id,
                'created_at' => $visitor->created_at,
                'last_active' => $visitor->last_active
            ]);
        } catch (\Exception $e) {
            return $this->sendErrorResponse('Error retrieving visitor: ' . $e->getMessage());
        }
    }

    /**
     * Send error response
     * 
     * @param mixed $message
     * @param int $statusCode
     */
    private function sendErrorResponse($message, int $statusCode = 400)
    {
        $response = $this->app->response;
        $response->setStatusCode($statusCode);
        
        if (is_array($message)) {
            $errors = [];
            foreach ($message as $msg) {
                $errors[] = $msg->getMessage();
            }
            $response->setJsonContent([
                'error' => 'Validation Error',
                'message' => $errors
            ]);
        } else {
            $response->setJsonContent([
                'error' => 'Error',
                'message' => $message
            ]);
        }
        
        return $response;
    }
} 