/**
 * 2017-2022 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    MBE Worldwide
 * @copyright 2017-2024 MBE Worldwide
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of MBE Worldwide
 */
 
window.addEventListener('DOMContentLoaded', function () {
    if (typeof gtmDataLayer === 'undefined') {
        //console.log('dataLayer is not defined')
        return
    }

    //console.log('dataLayer is defined', gtmDataLayer)

    let welcome_page = document.querySelector('#mbeshipping_conf_content .tab-pane#welcome')
    if (!welcome_page) {
        return;
    }

    if (welcome_page.classList.contains('active')) {
        gtmDataLayer.push({
            'event': 'homepage',
            'homepage': 'homepage'
        })
    }

    let observer = new MutationObserver((mutationsList, observer) => {
        for (const mutation of mutationsList) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                if (mutation.target.classList.contains('active')) {
                    gtmDataLayer.push({
                        'event': 'homepage',
                        'homepage': 'homepage'
                    })
                }
            }
        }
    });

    observer.observe(welcome_page, {attributes: true});

    let cta_login = welcome_page.querySelector('#btn_login_mbe_user')
    cta_login.addEventListener('click', function () {
        gtmDataLayer.push({
            'event': 'homepage',
            'login': 'login'
        })
    })

    let cta_register = welcome_page.querySelector('#btn_register_mbe_user')
    cta_register.addEventListener('click', function () {
        gtmDataLayer.push({
            'event': 'homepage',
            'register': 'register'
        })
    })

    let cta_start_your_experience_now = welcome_page.querySelector('#btn_start_your_experience_now')
    cta_start_your_experience_now.addEventListener('click', function () {
        gtmDataLayer.push({
            'event': 'homepage',
            'start_your_experience_now': 'start_your_experience_now'
        })
    })
})
