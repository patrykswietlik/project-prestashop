{*
 * BlueMedia_BluePayment extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GNU Lesser General Public License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/lgpl-3.0.en.html
 *
 * @category       BlueMedia
 * @package        BlueMedia_BluePayment
 * @copyright      Copyright (c) 2015-2024
 * @license        https://www.gnu.org/licenses/lgpl-3.0.en.html GNU Lesser General Public License
*}
<html>
<head>
    <link rel="stylesheet" href="{$bm_dir}views/css/front.css" />
    <title>{l s='Autopay redirect...' mod='bluepayment'}</title>
</head>
<body class="blue-media-body">
<div class="bm-redirect-page">
    <div class="bm-redirect-page-content">
        <img src="{$bm_dir}views/img/blue-media.svg" class="logo" />
        <p>{l s='Redirection to payment in progress.' mod='bluepayment'}</p>
        <p>{l s='Please wait a moment...' mod='bluepayment'}</p>
        <img src="{$bm_dir}views/img/redirect.gif" class="loader" />
    </div>

    <div>
        {$form nofilter}
    </div>
</div>
</body>
</html>
