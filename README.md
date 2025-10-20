E-Ticaret Backend · Laravel 12 · Onion/Clean · JWT · Event-Driven

Laravel 12 ile yazılmış, JWT kimlik doğrulama, rol bazlı yetki, Sepet → Sipariş → Ödeme akışı, stok tutarlılığı (transaction & atomic), RFC7807 Problem+JSON hata sözleşmesi, event/listener temelli sipariş durumu ve raporlama içeren ölçeklenebilir e-ticaret API’si.

<p align="left"> <img src="README-assets/screenshots/admin_login.png" alt="Admin Login (JWT ile giriş)" width="420"> <img src="README-assets/screenshots/sepet.png" alt="Sepet: ekleme/güncelleme" width="420"> </p> <p align="left"> <img src="README-assets/screenshots/sepet_onay.png" alt="Sepet onayı → siparişe dönüşüm" width="420"> <img src="README-assets/screenshots/odeme.png" alt="Ödeme: durum akışı" width="420"> </p>
İçindekiler

Özellikler

Mimari & Dizin Yapısı

Kurulum

.env Şablonu

Veritabanı & İlişkiler

Rotalar (Gerçek Yapıya Göre)

Sepet Sahipliği & Policy

Sipariş & Ödeme (Event-Driven)

Generic Repository (paginate/search/filter/orderBy)

Hata Yönetimi (RFC7807 Problem+JSON)

Gözlemlenebilirlik (AttachLogContext)

Test & Kalite

Yol Haritası

Lisans

Özellikler

🔐 JWT + Role middleware: admin, seller, customer rolleri. Route bazında auth:api + role:....

🧅 Onion/Clean: Controller ince; iş mantığı Service’te; veri erişimi Repository’de.

🛒 Sepet sahipliği: Kullanıcı sadece kendi sepetini görür/işler (admin istisnası).

📑 Sipariş yaşam döngüsü: sepet → sipariş, PaymentCompleted ile otomatik tamamlama, status log.

📉 Stok tutarlılığı: DB::transaction içinde atomic decrement()/increment().

🧰 Generic Repository: paginate / search / filter / orderBy tek merkezden.

🚦 RFC7807 uyumlu tek tip hata yanıtı + trace_id.

🔔 Notifications & Queue: OrderCompletedNotification (listener üzerinden tetiklenir).

📊 Raporlama: satış özetleri, en çok satanlar.

Mimari & Dizin Yapısı

Gerçek proje ağacına göre:

app/
├─ Core/
│  ├─ Entities/
│  │  ├─ Cart.php           ├─ CartItem.php
│  │  ├─ Category.php       ├─ Order.php
│  │  ├─ OrderItem.php      ├─ OrderStatusLog.php
│  │  ├─ Payment.php        └─ Product.php
│  ├─ Interfaces/
│  │  ├─ IBaseRepository.php      ├─ ICartRepository.php
│  │  ├─ ICategoryRepository.php  ├─ IOrderRepository.php
│  │  ├─ IPaymentRepository.php   ├─ IProductRepository.php
│  │  └─ IReportRepository.php
│  └─ Services/
│     ├─ CartService.php  ├─ CategoryService.php
│     ├─ OrderService.php ├─ PaymentService.php
│     ├─ ProductService.php
│     └─ ReportService.php
├─ Events/
│  ├─ OrderCreated.php
│  └─ PaymentCompleted.php
├─ Http/
│  ├─ Controllers/
│  │  ├─ AuthController.php   ├─ CartController.php
│  │  ├─ CategoryController.php
│  │  ├─ OrderController.php  ├─ PaymentController.php
│  │  ├─ ProductController.php└─ ReportController.php
│  ├─ Middleware/
│  │  ├─ AttachLogContext.php
│  │  └─ CheckRole.php
│  └─ Policies/
│     └─ CartPolicy.php
├─ Infrastructure/
│  └─ Repositories/
│     ├─ BaseRepository.php   ├─ CartRepository.php
│     ├─ CategoryRepository.php
│     ├─ OrderRepository.php  ├─ PaymentRepository.php
│     ├─ ProductRepository.php└─ ReportRepository.php
├─ Listeners/
│  ├─ SendOrderNotification.php
│  └─ UpdateOrderStatusOnPayment.php
├─ Notifications/
│  └─ OrderCompletedNotification.php
└─ Providers/
   ├─ AppServiceProvider.php
   └─ JwtServiceProvider.php
database/
├─ migrations/  # users, categories, products, orders, order_items, payments, carts, order_status_logs...
└─ seeders/
routes/
└─ api.php


İlke: Controller → Service → Repository → Model (Entities).
Bağımlılık tersine çevrimi: Controller’lar Interface’lere karşı programlar; binding AppServiceProvider’da.

Kurulum

Gereksinimler: PHP 8.2+, Composer, MySQL 8+ (veya SQLite), (ops) Redis, Node sadece Swagger UI istersen.

composer install

cp .env.example .env
php artisan key:generate

# MySQL kullanıyorsan:
php artisan migrate --seed

# (SQLite tercih edenler için alternatif)
# touch database/database.sqlite
# .env içinde DB_CONNECTION=sqlite yap
# php artisan migrate --seed

# JWT paketini hazırla (paket kurulu varsayılır)
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider" --force
php artisan jwt:secret

php artisan serve
# (ops) queue
php artisan queue:work

.env Şablonu
APP_NAME=EcommerceAPI
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ecommerce
DB_USERNAME=root
DB_PASSWORD=secret

# JWT
JWT_TTL=120

# Mail (geliştirme)
MAIL_MAILER=log

Veritabanı & İlişkiler

Order (1) — (N) OrderItem

Cart (1) — (N) CartItem, Cart.user_id sahipliği

OrderStatusLog: order_id, old_status, new_status, changed_by, created_at

Payment — Order: payments.order_id; durum değişiminde event tetiklenir

Tutarlılık:

createFromCart siparişi DB::transaction içinde oluşturur, stokları decrement eder, sepeti boşaltır.

İptal/iade durumlarında stok increment ile geri alınır.

Rotalar (Gerçek Yapıya Göre)

routes/api.php’den öne çıkanlar:

// Auth
Route::prefix('auth')->group(function () {
  Route::post('register', [AuthController::class, 'register']);
  Route::post('login',    [AuthController::class, 'login']);
  Route::middleware('auth:api')->group(function () {
    Route::get('me',      [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh',[AuthController::class, 'refresh']);
  });
});

// Categories (delete: admin; create/update: admin,seller)
Route::prefix('categories')->group(function () {
  Route::get('/',    [CategoryController::class, 'index']);
  Route::get('/{id}',[CategoryController::class, 'show']);
  Route::post('/',   [CategoryController::class, 'store'])->middleware(['auth:api','role:admin,seller']);
  Route::put('/{id}',[CategoryController::class, 'update'])->middleware(['auth:api','role:admin,seller']);
  Route::delete('/{id}',[CategoryController::class, 'destroy'])->middleware(['auth:api','role:admin']);
});

// Products (GET açık; create/update: auth; delete: admin)
Route::prefix('products')->group(function () {
  Route::get('/',      [ProductController::class, 'index']);
  Route::post('/',     [ProductController::class, 'store'])->middleware('auth:api');
  Route::put('/{id}',  [ProductController::class, 'update'])->middleware('auth:api');
  Route::delete('/{id}',[ProductController::class,'destroy'])->middleware(['auth:api','role:admin']);

  // gelişmiş
  Route::get('/search',            [ProductController::class, 'search']);
  Route::get('/filter',            [ProductController::class, 'filterByCategory']);
  Route::get('/paginate',          [ProductController::class, 'paginate']);
  Route::get('/filter-paginate',   [ProductController::class, 'paginateWithFilters']);

  // dinamik rota EN SONA
  Route::get('/{id}', [ProductController::class, 'show']);
});

// Orders (auth:api)
Route::prefix('orders')->middleware('auth:api')->group(function () {
  Route::get('/',      [OrderController::class, 'index']);
  Route::get('/{id}',  [OrderController::class, 'show']);
  Route::post('/',     [OrderController::class, 'store']);

  Route::get('/all',   [OrderController::class, 'all'])->middleware('role:admin');
  Route::get('/user',  [OrderController::class, 'getByUser'])->middleware('role:admin');
  Route::put('/{id}/status', [OrderController::class, 'updateStatus'])->middleware('role:admin');
  Route::get('/{id}/logs',   [OrderController::class, 'logs']);
});

// Orders → create from cart
Route::prefix('orders')->middleware('auth:api')->group(function () {
  Route::post('/from-cart', [OrderController::class, 'createFromCart']);
});

// Payments (auth:api; status update: admin)
Route::prefix('payments')->middleware('auth:api')->group(function () {
  Route::post('/', [PaymentController::class, 'store']);
  Route::get('/{orderId}', [PaymentController::class, 'show']);
  Route::put('/{id}/status', [PaymentController::class, 'updateStatus'])->middleware('role:admin');
});

// Cart (auth:api)
Route::middleware('auth:api')->group(function () {
  Route::get('/cart',               [CartController::class, 'index']);
  Route::post('/cart/items',        [CartController::class, 'store']);
  Route::match(['put','patch'], '/cart/items/{id}', [CartController::class, 'update']);
  Route::delete('/cart/items/{id}', [CartController::class, 'destroy']);
  Route::delete('/cart',            [CartController::class, 'clear']);
});

// Health / error demos
Route::get('/ok', fn() => tap(['ok'=>true], fn()=>Log::info('ok')));
Route::get('/crash', fn() => throw new \RuntimeException('Manual crash'));
Route::post('/validate-test', function (Request $r) {
  $r->validate(['name'=>'required']); return ['ok'=>true];
});


Not: Dinamik rota (/products/{id}) çakışmasın diye en sona tanımlanmıştır.

Sepet Sahipliği & Policy

CartPolicy ve controller seviyesinde kullanıcı eşleştirmesi ile güvence altındadır.

Kullanıcı, sadece cart.user_id === Auth::id() olan kayıtlara erişebilir.

Admin istisnası mevcuttur. Başkasının cart_item.id’ine erişim: 404 not_found.

Örnek istek:

# Ürün ekle
curl -X POST http://127.0.0.1:8000/api/cart/items \
  -H "Authorization: Bearer <TOKEN>" -H "Content-Type: application/json" \
  -d '{"product_id":2,"quantity":1}'

Sipariş & Ödeme (Event-Driven)

POST /api/orders/from-cart → Order(status=pending) + stok decrement + sepet boşaltma.

Ödeme onayı PUT /api/payments/{id}/status { status: "paid" } ile gelir.

Event: PaymentCompleted yayımlanır → UpdateOrderStatusOnPayment listener’ı
Order.status='completed' yapar ve OrderStatusLog kaydı ekler.

İptal/iade durumunda stoklar increment ile geri verilir.

Örnek ödeme akışı:

# 1) Ödeme oluştur
curl -X POST http://127.0.0.1:8000/api/payments \
  -H "Authorization: Bearer <TOKEN>" -H "Content-Type: application/json" \
  -d '{"order_id": 15, "amount": 199.9, "method": "card"}'

# 2) Admin ödeme durumunu onaylar
curl -X PUT http://127.0.0.1:8000/api/payments/7/status \
  -H "Authorization: Bearer <ADMIN_TOKEN>" -H "Content-Type: application/json" \
  -d '{"status": "paid"}'
# → Event tetiklenir, Order.completed olur, log yazılır

Generic Repository (paginate/search/filter/orderBy)

Sözleşmeler app/Core/Interfaces/*Repository.php, implementasyonlar app/Infrastructure/Repositories.
Tekrarlayan sorgular BaseRepository üzerinden çözümlenir.

Desteklenen özellikler:

paginate($perPage) → Laravel LengthAware JSON (current_page, total, …)

search($q) → ad/description LIKE %q%

filter([...]) → category_id, min_price, max_price, vs.

orderBy($field,$dir) → örn. price, desc

Örnekler

GET /api/products/search?q=kahve
GET /api/products/filter?category_id=1&min_price=50&max_price=200
GET /api/products?order_by=price&direction=desc&page=2


Kazanımlar: Controller sade kalır, Service iş kuralına odaklanır; yeni entity eklemek kolaylaşır.

Hata Yönetimi (RFC7807 Problem+JSON)

Üretimde APP_DEBUG=false iken ham stack gizlenir; her hata tek tip Problem+JSON döner:

{
  "status": 422,
  "code": "validation_error",
  "message": "The given data was invalid.",
  "trace_id": "8f7c1a0d-...",
  "errors": { "name": ["The name field is required."] }
}


Hızlı kontroller

GET /api/does-not-exist → 404 route_not_found

POST /api/validate-test (bodysiz) → 422 validation_error

GET /api/crash → 500 server_error (tek tip JSON)

Gözlemlenebilirlik (AttachLogContext)

AttachLogContext middleware’i her isteğe bağlamsal metaveri ekler: trace_id, kullanıcı kimliği, IP, method, path.
Loglar bu alanlar ile zenginleştiği için Postman → X-Request-Id: {{$guid}} ekleyerek uçtan uca korelasyon yapılabilir.

Test & Kalite
php artisan test             # PHPUnit/Pest
# (ops) PHPStan/Larastan, PHP-CS-Fixer konfigleri eklenebilir


CI (öneri): .github/workflows/ci.yml ile
composer install --no-interaction --prefer-dist, php -v, php artisan test, phpstan.

Yol Haritası

 Ürün varyant/atribüt (SKU, seçenekler)

 Redis cache (listeleme & raporlar)

 Rate limiting + IP bazlı koruma

 Soft delete + audit genişletme (Order/Payment/Product)

 S3/Local storage ile ürün görsel yükleme

 Swagger UI + Postman koleksiyonu yayınlama

Lisans

MIT

Hızlı Başlangıç Örnekleri
# Login → Token al
curl -X POST http://127.0.0.1:8000/api/auth/login \
 -H "Accept: application/json" \
 -d '{"email":"admin@example.com","password":"secret"}'

# Ürün liste
curl http://127.0.0.1:8000/api/products

# Sepete ekle (auth gerekli)
curl -X POST http://127.0.0.1:8000/api/cart/items \
 -H "Authorization: Bearer <TOKEN>" -H "Content-Type: application/json" \
 -d '{"product_id":2,"quantity":1}'

# Sepetten sipariş oluştur (auth)
curl -X POST http://127.0.0.1:8000/api/orders/from-cart \
 -H "Authorization: Bearer <TOKEN>"
