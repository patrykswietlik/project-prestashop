<?php
/**
 * NOTICE OF LICENSE
 * This source file is subject to the GNU Lesser General Public License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/lgpl-3.0.en.html
 *
 * @author     Autopay S.A.
 * @copyright  Since 2015 Autopay S.A.
 * @license    https://www.gnu.org/licenses/lgpl-3.0.en.html GNU Lesser General Public License
 */

declare(strict_types=1);

namespace BluePayment\Hook;

if (!defined('_PS_VERSION_')) {
    exit;
}
abstract class AbstractHook
{
    public const AVAILABLE_HOOKS = [];

    /**
     * @var \BluePayment
     */
    protected $module;

    protected $configuration;

    /**
     * @var \Context
     */
    protected $context;

    public function __construct(\BluePayment $module, $configuration)
    {
        $this->module = $module;
        $this->configuration = $configuration;
        $this->context = $module->getContext();
    }

    /**
     * @return array
     */
    public function getAvailableHooks(): array
    {
        return static::AVAILABLE_HOOKS;
    }
}
