<?php
/**
 * @author Przelewy24
 * @copyright Przelewy24
 * @license https://www.gnu.org/licenses/lgpl-3.0.en.html
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
?>
<div class="p24-step p24-step-3">
    <?php $content = []; ?>
    <p>
        Klucz API - dodatkowe funkcje
    </p>
    <p>
        Jakie dodatkowe funkcje i korzyści daje nam wtyczka Przelewy24 po wprowadzeniu klucza API?
    </p>
    <b>Oneclick</b>
    <p>
        Najwygodniejszy dla klienta sposób zakupu produktów,
        usług lub subskrypcji w Internecie za pomocą jednego
        kliknięcia, bez konieczności zakładania konta w systemie
        operatora i każdorazowego uzupełniania danych karty.
    </p>

    <b>
        Wybór metody płatności w sklepie
    </b>
    <p>
        Możliwość personalizacji procesu płatności, dowolność
        ingerencji w kwestie graficzne i funkcjonalne modułu.
        Wybór płatności bezpośrednio na stronie partnera.
    </p>
    <b>
        IVR
    </b>
    <p>
        Obsługa płatności przez telefon za pośrednictwem automatycznego
        operatora. Usługa ma zastosowanie m.in w serwisach,
        które posiadają własne CallCenter sprzedażowe lub doradcze.
        Klient w procesie płatności bezpiecznie uzupełnia dane swojej
        karty przy pomocy klawiatury własnego urządzenia.
    </p>
    <div class="p24-inputs-group">
        <p>
            <label class="">
                <span>
                    <?php echo $content['translations']['api_key']; ?>
                </span>
                <input name="p24_api_key" value="" class="" type="text"/>
            </label>
        </p>
    </div>
</div>
