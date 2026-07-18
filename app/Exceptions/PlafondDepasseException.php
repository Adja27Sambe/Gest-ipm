<?php

namespace App\Exceptions;

use Exception;

class PlafondDepasseException extends Exception
{
    protected $montantDepassement;

    public function __construct(string $message, float $montantDepassement = 0, int $code = 400, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->montantDepassement = $montantDepassement;
    }

    public function getMontantDepassement(): float
    {
        return $this->montantDepassement;
    }

    /**
     * Rapport d'exception pour API
     */
    public function render($request)
    {
        if ($request->is('api/*') || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => $this->getMessage(),
                'depassement' => $this->getMontantDepassement(),
            ], $this->code);
        }

        return false;
    }
}
