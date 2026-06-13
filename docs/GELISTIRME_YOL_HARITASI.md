# GLV Gelistirme Yol Haritasi

Bu dokuman GLV'yi Glovo/Korpa benzeri, Sarajevo'da baslayip diger Bosna Hersek sehirlerine buyuyebilecek bir teslimat platformuna cevirmek icin pratik gelistirme sirasi onerir.

## Mevcut durum

Projede su anda guclu bir backend baslangici var:

- Laravel API iskeleti
- PostgreSQL, Redis, Docker, nginx ve Reverb altyapisi
- Sanctum tabanli auth baslangici
- Musteri, kurye, restoran ve admin rolleri
- Sehir, restoran, sube, siparis, siparis kalemi ve kurye atama modelleri
- Temel siparis yasam dongusu
- Canli kurye konumu ve siparis durumu broadcast olaylari
- Tenant/rol bazli yetkilendirme
- CI ve security workflow dosyalari

Bu henuz tam urun degil. Su anki seviye: backend MVP temeli.

## Oncelik 1: Temeli stabil hale getirme

Hedef: Her gelistirici projeyi kolayca calistirsin ve testler guvenilir olsun.

- GitHub Actions workflow onaylarini tamamla.
- CI calistiktan sonra test hatalarini kapat.
- Local kurulum scriptlerini repo'ya ekle.
- Demo seed verilerini ekle.
- README ve kurulum dokumanini guncel tut.
- API icin Postman, Bruno veya OpenAPI dokumani hazirla.

Basari kriteri:
- Yeni biri repo'yu indirip tek komutla local API'yi calistirabilir.
- Demo admin, restoran, musteri ve kurye kullanicilari hazirdir.
- `php artisan test` basarili calisir.

## Oncelik 2: Restoran ve menu modeli

Hedef: Musteri gercek urunleri gorebilsin ve siparis verebilsin.

Eklenmesi gerekenler:

- Menu kategori modeli
- Urun modeli
- Urun fiyatlari
- Urun aktif/pasif durumu
- Opsiyon/modifier modeli
- Ekstra malzeme ve secenekler
- Restoran acik/kapali durumu
- Minimum sepet tutari
- Tahmini hazirlanma suresi

API alanlari:

- Restoran listeleme
- Restoran detay
- Menu listeleme
- Urun detay
- Restoran paneli icin menu CRUD

## Oncelik 3: Sepet ve checkout

Hedef: Musteri uygulamasinin ana akisi calissin.

Eklenmesi gerekenler:

- Sepet validasyonu
- Urun fiyat snapshot mantigi
- Teslimat adresi
- Mesafe bazli teslimat ucreti
- Servis ucreti
- Kupon/kampanya altyapisi
- Siparis olusturma oncesi fiyat hesaplama endpoint'i

Basari kriteri:
- Musteri restoran secip urunleri sepete ekler.
- Sistem toplam tutari dogru hesaplar.
- Siparis olustugunda restoran onayina duser.

## Oncelik 4: Kurye operasyonu

Hedef: Siparis restorandan cikinca kurye sureci guvenilir calissin.

Gelistirilecek konular:

- En yakin kurye secimi
- Online ve musait kurye ayrimi
- Ayni kuryeye ayni anda fazla is yuklenmemesi
- Kurye red ederse yeniden atama
- Zaman asimi
- Kurye kabul ettikten sonra rota durumu
- Pickup ve teslimat adimlari
- Kurye performans metrikleri

Basari kriteri:
- Kurye online oldugunda siparis teklifi alir.
- Kabul/red akisina gore siparis durumu dogru ilerler.
- Konum guncellemeleri canli takip ekranina akar.

## Oncelik 5: Paneller

Hedef: Operasyon insanlar tarafindan yonetilebilir hale gelsin.

Admin panel:

- Sehir yonetimi
- Restoran yonetimi
- Sube yonetimi
- Kurye aktivasyon
- Siparis takip
- Problemli siparis aksiyonlari

Restoran panel:

- Menu yonetimi
- Siparis listesi
- Siparis kabul/red
- Hazirlandi bildirimi
- Calisma saatleri

Teknik onerim:
- Admin panel icin Laravel Filament hizli ve mantikli secim olur.
- Restoran panel de ilk etapta Filament veya sade Laravel/Vue panel olabilir.

## Oncelik 6: Mobil uygulamalar

Flutter tarafinda 3 ayri deneyim dusunulmeli:

- Musteri uygulamasi
- Kurye uygulamasi
- Restoran operasyon uygulamasi veya responsive web panel

Musteri uygulamasi MVP:

- Login/register
- Sehir secimi
- Restoran listeleme
- Menu
- Sepet
- Siparis olusturma
- Canli siparis takip

Kurye uygulamasi MVP:

- Login
- Online/offline
- Siparis teklifi
- Kabul/red
- Konum gonderimi
- Pickup/teslimat adimlari

## Oncelik 7: Odeme ve ticari model

Bosna Hersek pazari icin odeme stratejisi erken netlesmeli.

Ilk etap:

- Kapida odeme
- Nakit odeme tipi
- Restoran komisyon oranlari
- Teslimat ucreti kurallari

Sonraki etap:

- Kart odeme entegrasyonu
- Iade akislari
- Kupon ve promosyon
- Restoran cari hesap raporlari
- Kurye kazanc raporlari

## Teknik kalite notlari

- Controller'lar ince kalmali.
- Is kurallari service/action katmaninda olmali.
- Kritik akislarda test sart: auth, tenant isolation, checkout, kurye atama.
- Realtime event payload'lari versiyonlanabilir dusunulmeli.
- API response formatlari standardize edilmeli.
- Rate limiting ve audit log eklenmeli.
- Production ortaminda queue worker, scheduler ve reverb ayri servisler olarak yonetilmeli.

## En yakin 10 is

1. PR'i merge edilebilir hale getir.
2. CI workflow'larini calistir ve duzelt.
3. Local kurulum dosyalarini ekle.
4. Demo seed data ekle.
5. API dokumani baslat.
6. Menu/category/product migrationlarini tasarla.
7. Restoran listeleme ve menu endpointlerini yaz.
8. Checkout fiyat hesaplama endpointini yaz.
9. Siparis olusturma akisini menu verisine bagla.
10. Admin panel icin Filament kurulumunu planla.
