# GLV – Bosnia Premium Delivery Platform (Architecture First)

Bu depo, **Sarajevo ile başlayıp Mostar, Tuzla ve diğer şehirlere ölçeklenebilen** API-first bir teslimat platformunun ilk faz temelini içerir.

## 0) Koddan Önce Mimari

### Mimari yaklaşım
- **API First**: Tüm iş kuralları REST API üzerinden sunulur (Flutter sadece API tüketir).
- **Thin Controller + Service Layer**: Controller yalnızca request/response yönetir; iş kuralları service katmanındadır.
- **Event-Driven + Queue**: Ağır işler event/listener/job ile asenkron yürütülür (Redis queue).
- **Multi-city / Multi-branch**: Her restoran birden çok şubeye ve şehirye bağlanır.
- **Future-ready verticals**: Sipariş `domain_type` alanı ile yemek dışı (market/eczane) genişlemesine hazırdır.

## 1) Klasör Yapısı

```text
app/
  Http/Controllers/Api/V1/
  Services/
    Auth/
    Orders/
    Courier/
  Events/Orders/
  Listeners/Orders/
  Jobs/Orders/
database/migrations/
routes/
```

## 2) Veritabanı Tasarımı (Özet)

- `cities`
- `restaurants`
- `restaurant_branches` (şehir bazlı)
- `users` (role: customer/courier/restaurant/admin)
- `courier_profiles`
- `orders` (multi-city, multi-domain)
- `order_items`
- `courier_assignments`

## 3) Migration Dosyaları

`database/migrations/` altında ilk çekirdek migration dosyaları oluşturuldu.

## 4) Auth Sistemi

- Token tabanlı API auth için service/controller iskeleti eklendi.
- Rollere göre yetkilendirme akışı için temel metotlar eklendi.

## 5) Sipariş Sistemi

- `OrderService` ile sipariş oluşturma akışı service katmanına alındı.
- `OrderCreated` event’i tetiklenir; restoran bildirimi listener üzerinden çalışır.

## 6) Kurye Atama Sistemi

- `AssignCourierJob` ve `CourierAssignmentService` ile kuyruk tabanlı atama akışı iskeletlendi.
- Canlı takip için konum verisi `courier_profiles.last_lat/lng` alanları üzerinden modellenmiştir.

## İlk Faz Notu

Bu faz, üretim koduna geçiş için mimari ve çekirdek domain omurgasını sağlar. Bir sonraki fazda endpoint doğrulamaları, policy/permission, websocket canlı takip yayınları ve kapsamlı testler tamamlanmalıdır.
