<?php
/**
 * Class Przelewy24Service
 *
 * @author Przelewy24
 * @copyright Przelewy24
 * @license https://www.gnu.org/licenses/lgpl-3.0.en.html
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Przelewy24Service
 */
abstract class Przelewy24Service
{
    /**
     * Main Przelewy24 module class.
     *
     * @var Przelewy24
     */
    private $przelewy24;

    /**
     * Przelewy24Service constructor.
     *
     * @param Przelewy24 $przelewy24
     */
    public function __construct(Przelewy24 $przelewy24)
    {
        $this->przelewy24 = $przelewy24;
    }

    /**
     * Get module Przelewy24.
     *
     * @return Przelewy24
     */
    protected function getPrzelewy24()
    {
        return $this->przelewy24;
    }
}
