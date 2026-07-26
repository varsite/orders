# varsite/orders

Moduł zamówień dla Varsite Platform — **właściciel faktu „co i komu sprzedaliśmy"**.

```bash
composer require varsite/orders
php artisan varsite:module install orders
```

## Zamówienie jest dokumentem, nie rekordem

Dokumentu handlowego **nie edytuje się i nie usuwa**. API nie ma metod tworzenia
ani modyfikacji zamówienia — zmienia się wyłącznie **stan**, i tylko zgodnie
z dozwolonymi przejściami:

```
Szkic ──▶ Złożone ──▶ Opłacone ──▶ Zrealizowane
  └──────────┴────────────┴──▶ Anulowane
```

Stany końcowe (zrealizowane, anulowane) nie mają przejść wyjściowych.

## Zapis historyczny, nie kopia

Pozycja zamówienia utrwala **SKU, nazwę i cenę z chwili sprzedaży**. To nie jest
kopia danych katalogu, lecz własny fakt modułu: „sprzedano to, pod tą nazwą,
za tyle, wtedy".

| Zmiana w katalogu | Wpływ na zamówienie |
|---|---|
| przemianowanie produktu | żaden |
| podwyżka ceny | żaden |
| wycofanie z oferty | żaden |
| usunięcie produktu | żaden — brak klucza obcego |
| odinstalowanie modułu katalogu | żaden — brak zależności |

Test rozpoznawczy z N14: **kopia zmienia się razem ze źródłem, zapis historyczny
nigdy**.

## Czego moduł świadomie nie przechowuje

Aktualnej ceny (należy do cennika), aktualnej dostępności i stanów magazynowych
(należą do magazynu), danych produktu poza momentem sprzedaży (należą do katalogu).
Gdy zajdzie potrzeba tych informacji, moduł zapyta przez kontrakt — nie skopiuje
ich do siebie.

## Granice kontekstów

```
Catalog     — co oferujemy          (nie wie o zamówieniach)
Inventory   — ile fizycznie mamy    (nie wie o zamówieniach)
Pricing     — ile kosztuje          (nie wie o zamówieniach)
Orders      — co komu sprzedaliśmy  (nie zależy od żadnego z nich)
```

`composer.json` modułu wymaga **wyłącznie** `varsite/platform`. Zależność
w drugą stronę nie istnieje i nie powstanie: sprzedaż jest konsumentem
informacji, nie ich dostawcą.

## Zdarzenia

`OrderStatusChanged` niesie zamówienie oraz stan przed i po. Istnieje po to,
żeby inne konteksty mogły **zareagować** — wysłać wiadomość, wystawić dokument,
zwolnić rezerwację.

> **Zdarzenie nie jest mechanizmem synchronizacji.** Jeśli słuchacz zapisuje
> u siebie dane, które już istnieją w zamówieniu, to kopiowanie cudzego faktu,
> a nie reakcja biznesowa.

## API

```
GET   /api/v1/admin/orders              lista z wyszukiwaniem i filtrem stanu
GET   /api/v1/admin/orders/{id}         zamówienie z pozycjami
PATCH /api/v1/admin/orders/{id}/status  zmiana stanu (jedyna modyfikacja)
```

Uprawnienia: `orders.view`, `orders.update`. Celowo **brak** `orders.create`
i `orders.delete` — dokumenty powstają w procesie sprzedaży, nie w panelu.
