# Instrukcja dla modułu PrestaShop 1.7 i 8.x: "Płatności online Autopay" 

## Podstawowe informacje
Płatności online Autopay to moduł płatności umożliwiający realizację transakcji bezgotówkowych w sklepie opartym na platformie PrestaShop 1.7 oraz 8.x.

### Główne funkcje
Do najważniejszych funkcji modułu zalicza się:
- obsługa 99% dostępnych na rynku metod płatności;
- prosta aktywacja w zaledwie 15 minut;
- płatności zaszyte w ścieżce zakupowej sklepu;
- możliwość zmiany kolejności wyświetlania kanałów płatności;
- możliwość decydowania o tym, które metody płatności będą widoczne i dostępne dla klienta – w każdej chwili można włączyć i wyłączyć dany kanał;
- dostępność [Pay by link (PBL)](https://autopay.pl/baza-wiedzy/blog/ecommerce/platnosc-pay-by-link-na-czym-polega-i-co-mozesz-dzieki-niej-zyskac) – dane do przelewu generują się automatycznie, a klient nie ma możliwości ich edycji, dzięki czemu znika ryzyko wystąpienia błędów podczas zlecania transakcji;
- dostępność [Google Pay](https://autopay.pl/rozwiazania/google-pay) i [Apple Pay](https://autopay.pl/rozwiazania/apple-pay) – prostych i szybkich płatności z użyciem zapisanej karty;
- płatność kartą – klient uzupełnia niezbędne dane kartowe;
- płatność [BLIK 0](https://autopay.pl/rozwiazania/blik) – klient pozostaje w widoku koszyka sklepu internetowego i tam wpisuje 6-cyfrowy kod wygenerowany wcześniej w aplikacji mobilnej banku;
- możliwość integracji z systemem ratalnym Alior – szczególnie przydatne w przypadku sklepów sprzedających droższy asortyment;
- możliwość uruchomienia płatności odroczonych;
- płatności w wielu walutach: EUR, GBP, USD;
- możliwość realizacji zakupów bez rejestracji (jako gość);
- system notyfikacji e-mail informujących o zmieniającym się statusie transakcji;
- dwa tryby działania: testowy i produkcyjny;
- informacje o statusie płatności przekazywane niezwłocznie również do sprzedawcy;
- możliwość obsługi wielu sklepów.
### Wymagania
- Minimalna wersja PrestaShop: 1.7
- Wersja PHP zgodna z wymaganiami względem danej wersji sklepu

## Aktywacja płatności w panelu administracyjnym PrestaShop

Dzięki integracji Płatności Online Autopay i platformy PrestaShop – możesz aktywować naszą usługę bezpośrednio w swoim panelu administracyjnym lub [pobrać i zainstalować ją samodzielnie](https://github.com/bluepayment-plugin/prestashop-plugin-1.7#instalacja-wtyczki).

Postępuj zgodnie z poniższą instrukcją:

1. Zaloguj się do swojego panelu administracyjnego w PrestaShop.
2. Kliknij Moduły > Płatności i wpisz w wyszukiwarkę "Autopay"
3. Po wyszukaniu modułu płatności, kliknij Włącz
4. Żeby zakończyć aktywację usługi i móc przejść do Konfiguracji – zarejestruj się w systemie Autopay i przejdź proces [weryfikacyjny](https://developers.autopay.pl/online/wdrozenie-krok-po-kroku).

Po pozytywnej weryfikacji przez Autopay, płatności online zostaną aktywowane w panelu PrestaShop i będziesz mógł je skonfigurować zgodnie ze swoimi potrzebami.


## Instalacja wtyczki

1) Pobierz najnowszą wersję wtyczki z rozszerzeniem .zip klikając [tutaj](https://github.com/bluepayment-plugin/prestashop-plugin-1.7/releases).

2) Wejdź na http(s)://domena_sklepu.pl/nazwa_katalogu_administratora i zaloguj się do swojego konta administratora używając loginu i hasła.

![Logowanie](https://user-images.githubusercontent.com/87177993/130193963-b91d3de8-57a6-4c62-80e3-64a2ce93d735.png)

3) Po zalogowaniu się przejdź do zakładki **Moduły > Moduły i usługi** (lub **Menedżer modułów** – w zależności od wersji sklepu).
- kliknij **Dodaj nowy moduł** (widoczny w prawym górnym rogu), by wgrać paczkę plików, którą pobrałeś w poprzednim kroku;

![Dodaj nowy moduł](https://user-images.githubusercontent.com/87177993/130194405-94fe13b7-e2f8-4ea9-bea0-8db12ebeeb9e.png)

*(Po kliknięciu przycisku pojawi się okno umożliwiające wybór pliku z komputera.)*

- kliknij **Załaduj moduł**

Gdy instalacja się zakończy, system przeniesie cię automatycznie do Konfiguracji modułu.


## Konfiguracja

### Konfiguracja sklepu

1) Zaloguj się za pomocą konta administratora na adres:
   http(s)://domena_sklepu.pl/nazwa_katalogu_administratora

![Logowanie](https://user-images.githubusercontent.com/87177993/130193963-b91d3de8-57a6-4c62-80e3-64a2ce93d735.png)

2) Przejdź do zakładki **Preferencje ➝ Ruch**, znajdź **Przyjazny adres URL** i włącz klikając **Tak**.

![Przyjazny adres URL](https://user-images.githubusercontent.com/87177993/130194986-893c69f6-e47c-428d-884e-44e8ffcfc6e6.png)


### Konfiguracja modułu

1) Przejdź do zakładki **Moduły > Moduły i usługi** (lub **Menedżer modułów** – w zależności od wersji sklepu) i wybierz z listy modułów kategorię: **Płatność** (lub wyszukaj moduł za pomocą wyszukiwarki).
2) Kliknij przycisk Konfiguruj w bloku o nazwie **Płatności Autopay** i uzupełnij wszystkie dane (otrzymasz je od nas). Jeżeli przycisk **Konfiguruj** nie jest widoczny – należy ponownie zainstalować moduł.
3) Żeby uzyskać od nas **Identyfikator serwisu partnera** oraz **Klucz konfiguracyjny (hash)** – prześlij do nas adresy do komunikacji między sklepem a bramką płatniczą:
- http(s)://domena_sklepu.pl/module/bluepayment/back
- http(s)://domena_sklepu.pl/module/bluepayment/status


## Ustawienia
### Zakładka uwierzytelnianie

1. Tryb testowy – zmiana trybu pracy bramki na testowy umożliwia weryfikację działania modułu bez konieczności rzeczywistego opłacania zamówienie (w trybie testowym nie pobierane są żadne opłaty za zamówienie).
2. Identyfikator serwisu – składa się tylko z cyfr i jest inny dla każdego sklepu (uzyskasz go od Autopay).
3. Klucz konfiguracyjny (hash) – służy do weryfikacji komunikacji z bramką płatności. Zawiera cyfry i małe litery. Nie należy go udostępniać publicznie (uzyskasz go od Autopay).

Jeśli masz więcej niż jedną walutę w sklepie, to pola Identyfikator i Klucz konfiguracyjny (hash) będą powielone, żeby można było je przypisać do każdej z walut.

### Zakładka płatności
1. Pokazuj metody płatności w sklepie – gdy opcja jest włączona, klient zobaczy wszystkie dostępne metody płatności (np. BLIK, przelew internetowy, itp.) już na stronie sklepu. Dzięki temu łatwiej i szybciej wybierze tę dogodną dla siebie.
2. Nazwa modułu płatności w sklepie – jak ma zostać nazwana płatność w której wybieramy bank, za pomocą którego płaci klient.
3. Lista kanałów płatności – wyświetlona jest lista dostępnych bramek, pozwala na ustawianie kolejności banków metodą 'przeciągnij i upuść'.
4. Ustawienia przekierowań płatności – pozwala na ustawienie czy płatność ma odbyć się bez opuszczania sklepu (ustawienie w pozycji włączony) lub z opuszczeniem sklepu do strony bramki płatności Autopay (ustawienie w pozycji wyłączony).
5. Statusy płatności:
- Płatność rozpoczęta – status zamówienia w sklepie – ustawiany natychmiast po rozpoczęciu płatności.
- Płatność zatwierdzona – status zamówienia w sklepie – ustawiany po potwierdzeniu płatności.
- Płatność nieudana – status ustawiany w przypadku niepowodzenia płatności lub gdy płatności nie została zrealizowana przez dłuży czas (czas ten ustalamy dla każdego sklepu indywidualnie).

### Zakładka analityka
Rozszerzyliśmy możliwości analityczne modułu, poprzez podłączenie dodatkowych zdarzeń w Google Analytics. Żeby z nich korzystać, należy dodać Identyfikator konta Google w widocznym poniżej polu.

Dzięki połączeniu możesz poznać dokładną ścieżkę zakupową klienta oraz sprawdzić jego zachowania na poszczególnych etapach procesu i na tej podstawie podjąć działania optymalizujące działanie sklepu.

![Analityka](https://raw.githubusercontent.com/bluepayment-plugin/prestashop-plugin-1.7/master/docs/img/analityka.png)

### Logi

W przypadku pojawienia się błędów podczas przetwarzania transakcji zapisywana jest odpowiednia informacja, która ma pomóc w szybszym odnalezieniu przyczyny problemu.

Żeby przejrzeć logi – przejdź do zakładki **Zaawansowane > Logi** i uzupełnij następujące filtry:
- Wiadomość BM

### Zamówienia

W podglądzie zamówienia, w sekcji **Zamówienie** dodawane są wpisy związane z informacjami na temat przebiegu procesu transakcji.

### Transakcje i faktury

Tworzone są automatycznie w zależności od ustawień statusów transakcji.

### Powiadomienia mailowe

Powiadomienia o zmianie statusu płatności wysyłane są w zależności od konfiguracji danego statusu. Jeżeli chcesz, żeby powiadomienia były wysyłane – zaznacz opcję **Wyślij email do klienta, kiedy zmieni się status zamówienia** (wybrany musi być również odpowiedni szablon).


## Wygląd kanałów płatności

- Metody płatności obsługiwane Autopay są zgrupowane i zaprezentowane w
  estetyczny, nowoczesny sposób:

![Payment](https://raw.githubusercontent.com/bluepayment-plugin/prestashop-plugin-1.7/master/docs/img/platnosci_front.png)
- Wybór metody płatności typu przelew internetowy lub wirtualny portfel jest bardzo prosty, dzięki wdrożeniu wygodnego okienka:

![Payment](https://raw.githubusercontent.com/bluepayment-plugin/prestashop-plugin-1.7/master/docs/img/platnosci_modal.png)
- Metody płatności Google Pay oraz Apple Pay zostały zgrupowane w pozycji Wirtualny portfel:

![Payment](https://raw.githubusercontent.com/bluepayment-plugin/prestashop-plugin-1.7/master/docs/img/platnosci_wirtualny_portfel.png)
- Wybraną metodę płatności możesz z łatwością zmienić. Nowy wygląd zapewnia również łatwy dostęp do niezbędnych informacji nt. przekierowań i regulaminów:

![Payment](https://raw.githubusercontent.com/bluepayment-plugin/prestashop-plugin-1.7/master/docs/img/platnosci_przelew.png)


## Aktualizacja

1) Żeby dokonać aktualizacji – Wystarczy kliknąć Upgrade.

![Upgrade](https://user-images.githubusercontent.com/87177993/130195194-14d14c9a-1cfa-43f8-aa4b-c82e72a28dac.png)

2) Następnie postępuj zgodnie z instrukcją opisaną w sekcji **Instalacja wtyczki**.

## Odinstalowanie
Żeby odinstalować moduł – wybierz **Odinstaluj**.

![Odinstaluj](https://user-images.githubusercontent.com/87177993/130195263-931b9ac8-a2f0-42c1-841f-0fc853a716b4.png)
