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

namespace BlueMedia\OnlinePayments\HttpClient;

if (!defined('_PS_VERSION_')) {
    exit;
}
class CurtHttpResponse
{
    private $code;
    private $body;

    public function __construct($code, $body)
    {
        $this->code = $code;
        $this->body = $body;
    }

    public function getStatusCode()
    {
        return $this->code;
    }

    public function getBody()
    {
        return $this->body;
    }
}
