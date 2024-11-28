Objaśniam jaka jest konwecja efektów scrappera:
Mamy 3 pliki:
1) categories.json - Kategorie zawiera nazwy kategorii i linki
2) productGrid.json - Produkty z siatki po wejsciu poprzez kategorie
3) productDetail.json - Szczegóły produktu strona produktu

1) Jest to lista słowników. Klucz "name" zawiera listę nazw kategorii/podkategorii. Pierwszy obiekt tej listy to zawsze nazwa kategorii reszta to nazwy podkategorii. Klucz "urls" zawiera listę linków do kategorii/podkategorii. Pierwszy obiekt tej listy to zawsze link kategorii reszta to linki podkategorii.

2)