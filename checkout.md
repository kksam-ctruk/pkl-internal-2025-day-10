# Hari 11: Checkout Process & Order

**⏱️ Durasi: 7 Jam**

***

## 🎯 Tujuan Pembelajaran

Setelah menyelesaikan materi hari ini, kamu akan mampu:

1. Membuat alur checkout multi-step
2. Mendesain skema database Order dan OrderItem
3. Mengimplementasikan **DB Transaction** untuk keamanan data pesanan
4. Mengurangi stok produk secara atomik saat pesanan dibuat
5. Menyimpan snapshot harga produk (agar perubahan harga masa depan tidak mengubah histori pesanan)

***

## 💡 Konsep Dasar (5W1H)

| Pertanyaan       | Jawaban & Analogi                                                                                                                                                    |
| ---------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Kenapa?**      | Cart hanyalah "Niat" beli. Order adalah "Janji Sah" beli. Kita perlu mengubah draft belanja menjadi tagihan resmi yang terkunci harganya.                            |
| **Seperti apa?** | Ini adalah **Meja Kasir**. Barang di keranjang dikeluarkan, di-scan barcode-nya, dihitung totalnya, lalu dicetak struk tagihannya.                                   |
| **Untuk apa?**   | Mencatat transaksi secara legal. Order menyimpan snapshot harga saat itu (kalau besok harga naik, user tetap bayar harga lama), alamat kirim, dan status pembayaran. |
| **Hasilnya?**    | Keranjang menjadi kosong, dan user mendapatkan nomor pesanan (misal: INV-2023001) beserta total yang harus dibayar.                                                  |
| **Contoh?**      | `DB::transaction(...)` akan memindahkan item dari tabel `carts` ke `order_items`, lalu menghapus isi `carts`. Stok produk dikurangi sesuai jumlah beli.              |

***

## 📚 Materi

### 1. Database Schema for Orders (1 jam)

Pesanan adalah entitas utama dalam e-commerce. Kita perlu menyimpan "Siapa yang pesan", "Kapan pesan", "Apa yang dipesan", dan "Berapa harganya saat itu".

#### 1.1 Tabel `orders`

Menyimpan informasi header pesanan.

```bash
php artisan make:migration create_orders_table
```

```php
Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained();
    $table->string('order_number')->unique(); // ID unik, misal ORD-20231201-001

    // Status Pesanan
    $table->enum('status', ['pending', 'processing', 'completed', 'cancelled'])->default('pending');

    // Status Pembayaran (PENTING: tambahkan ini)
    $table->enum('payment_status', ['unpaid', 'paid', 'failed'])->default('unpaid');

    // Informasi Pengiriman
    $table->string('shipping_name');
    $table->string('shipping_address');
    $table->string('shipping_phone');

    // Total & Biaya
    $table->decimal('total_amount', 12, 2);
    $table->decimal('shipping_cost', 12, 2)->default(0);

    // Midtrans Snap Token
    $table->string('snap_token')->nullable();

    $table->timestamps();
});
```

#### 1.2 Tabel `order_items`

Menyimpan rincian produk yang dibeli.

```bash
php artisan make:migration create_order_items_table
```

```php
Schema::create('order_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained()->onDelete('cascade');
    $table->foreignId('product_id')->constrained();

    // PENTING: Snapshot data produk saat transaksi
    $table->string('product_name'); // Simpan nama kalau-kalau produk dihapus/diubah
    $table->integer('quantity');
    $table->decimal('price', 12, 2); // Simpan harga SAAT transaksi, bukan relasi ke harga produk sekarang
    $table->decimal('subtotal', 12, 2); // quantity * price

    $table->timestamps();
});
```

### 2. Order Service Logic (2 jam)

Proses pembuatan order ("Place Order") melibatkan banyak langkah yang harus sukses semua atau gagal semua.

1. Validasi alamat.
2. Buat record `Order`.
3. Pindahkan item dari `Cart` ke `OrderItems`.
4. Kurangi stok `Product`.
5. Kosongkan `Cart`.

Kita akan gunakan **OrderService** untuk membungkus logika ini.

```php
<?php
// app/Services/OrderService.php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Models\Cart;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    /**
     * Membuat Order baru dari Keranjang belanja.
     *
     * ALUR PROSES (TRANSACTION):
     * 1. Hitung total & Validasi Stok terakhir
     * 2. Buat Record Order (Header)
     * 3. Pindahkan Cart Items ke Order Items (Detail)
     * 4. Kurangi Stok Produk (Atomic Decrement)
     * 5. Hapus Keranjang
     */
    public function createOrder(User $user, array $shippingData): Order
    {
        // 1. Ambil Keranjang User
        $cart = $user->cart;

        if (!$cart || $cart->items->isEmpty()) {
            throw new \Exception("Keranjang belanja kosong.");
        }

        // ==================== DATABASE TRANSACTION START ====================
        // Kita menggunakan DB::transaction untuk membungkus semua proses.
        // Jika ada 1 error saja (misal stok kurang saat mau decrement),
        // maka SEMUA query yang sudah jalan akan dibatalkan (Rollback).
        // Order tidak akan terbentuk setengah-setengah.
        return DB::transaction(function () use ($user, $cart, $shippingData) {

            // A. VALIDASI STOK & HITUNG TOTAL
            $totalAmount = 0;
            foreach ($cart->items as $item) {
                // Penting: Cek stok lagi sesaat sebelum memastikan order.
                // Mencegah "Race Condition" jika ada orang lain yang beli barang terakhir detik yang sama.
                if ($item->quantity > $item->product->stock) {
                    throw new \Exception("Stok produk {$item->product->name} tidak mencukupi.");
                }
                $totalAmount += $item->product->price * $item->quantity;
            }

            // B. BUAT HEADER ORDER
            $order = Order::create([
                'user_id' => $user->id,
                // Generate Order Number Unik. Contoh: ORD-X7Y8Z9A1B2
                'order_number' => 'ORD-' . strtoupper(Str::random(10)),
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'shipping_name' => $shippingData['name'],
                'shipping_address' => $shippingData['address'],
                'shipping_phone' => $shippingData['phone'],
                'total_amount' => $totalAmount,
            ]);

            // C. PINDAHKAN ITEMS
            foreach ($cart->items as $item) {
                // Buat Order Item
                $order->items()->create([
                    'product_id' => $item->product_id,

                    // SNAPSHOT DATA (PENTING!)
                    // Kita simpan nama & harga barang SAAT INI ke tabel order_items.
                    // Tujuannya: Jika besok admin ubah harga/nama produk,
                    // data di historical order user TIDAK IKUT BERUBAH.
                    'product_name' => $item->product->name,
                    'price' => $item->product->price,

                    'quantity' => $item->quantity,
                    'subtotal' => $item->product->price * $item->quantity,
                ]);

                // D. KURANGI STOK (ATOMIC)
                // decrement() menjalankan query: UPDATE products SET stock = stock - X
                // Ini thread-safe di level database.
                $item->product->decrement('stock', $item->quantity);
            }

            // E. BERSIHKAN KERANJANG
            // Hapus semua item di keranjang user karena sudah jadi order.
            $cart->items()->delete();

            // Opsional: Hapus object cart-nya juga jika ingin reset session total
            // $cart->delete();

            return $order;
        });
        // ==================== DATABASE TRANSACTION END ====================
    }
}
```

### 3. Checkout Controller & View (2 jam)

#### 3.1 Controller

```php
<?php
// app/Http/Controllers/CheckoutController.php

namespace App\Http\Controllers;

use App\Services\OrderService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function index()
    {
        // Pastikan keranjang tidak kosong
        $cart = auth()->user()->cart;
        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Keranjang kosong.');
        }

        return view('checkout.index', compact('cart'));
    }

    public function store(Request $request, OrderService $orderService)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
        ]);

        try {
            $order = $orderService->createOrder(auth()->user(), $request->only(['name', 'phone', 'address']));

            // Redirect ke halaman pembayaran (akan dibuat besok)
            // Untuk sekarang, redirect ke detail order
            return redirect()->route('orders.show', $order)
                ->with('success', 'Pesanan berhasil dibuat! Silahkan lakukan pembayaran.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
```

#### 3.2 View (`checkout/index.blade.php`)

Halaman checkout biasanya terbagi dua kolom:

1. **Kiri**: Form Alamat Pengiriman.
2. **Kanan**: Ringkasan Pesanan (Order Summary).

```php
{{-- resources/views/checkout/index.blade.php --}}

<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-8">Checkout</h1>

        <form action="{{ route('checkout.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                {{-- Form Alamat --}}
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h2 class="text-lg font-semibold mb-4">Informasi Pengiriman</h2>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nama Penerima</label>
                                <input type="text" name="name" value="{{ auth()->user()->name }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nomor Telepon</label>
                                <input type="text" name="phone"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Alamat Lengkap</label>
                                <textarea name="address" rows="3"
                                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Order Summary --}}
                <div class="lg:col-span-1">
                    <div class="bg-white p-6 rounded-lg shadow sticky top-4">
                        <h2 class="text-lg font-semibold mb-4">Ringkasan Pesanan</h2>

                        <div class="space-y-4 max-h-60 overflow-y-auto mb-4">
                            @foreach($cart->items as $item)
                                <div class="flex justify-between text-sm">
                                    <span>{{ $item->product->name }} x {{ $item->quantity }}</span>
                                    <span class="font-medium">{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="border-t pt-4 space-y-2">
                            <div class="flex justify-between text-base font-bold">
                                <span>Total</span>
                                <span>Rp {{ number_format($cart->items->sum('subtotal'), 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <button type="submit"
                                class="w-full mt-6 bg-indigo-600 text-white py-3 rounded-lg font-semibold hover:bg-indigo-700">
                            Buat Pesanan
                        </button>
                    </div>
                </div>

            </div>
        </form>
    </div>
</x-app-layout>
```

***

## 💻 Praktikum

### Tugas 1: Database Setup (45 menit)

1. Buat tabel `orders` dan `order_items`.
2. Pastikan tipe data `decimal` untuk uang.
3. Migrate database.

### Tugas 2: Order Service (1.5 jam)

1. Buat class `OrderService`.
2. Implementasikan logik transaksi database.
3. Pastikan stok berkurang setelah order dibuat.

### Tugas 3: Checkout Page (1.5 jam)

1. Buat controller dan view.
2. Tampilkan form alamat dan ringkasan belanja.
3. Test alur: Masukkan barang ke keranjang -> Checkout -> Cek database apakah order terbentuk dan cart kosong.

***

## 📝 Rangkuman

| Konsep               | Penjelasan                                                                                                                                                                                       |
| -------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **Snapshot Data**    | Menyimpan salinan data (seperti nama & harga produk) di tabel `order_items`. Ini penting agar jika harga produk berubah di masa depan, histori pesanan tetap akurat sesuai harga saat pembelian. |
| **DB Transaction**   | `DB::transaction(...)` memastikan integritas data. Jika pengurangan stok gagal, order tidak boleh terbentuk.                                                                                     |
| **Atomic Operation** | `decrement()` adalah operasi atomik di level database yang mencegah *race condition* jika dua user membeli barang terakhir bersamaan.                                                            |

***

## ⏭️ Hari Berikutnya

Pesanan sudah dibuat, tapi statusnya masih `unpaid`. Besok kita akan mengintegrasikan **Midtrans Payment Gateway** agar user bisa membayar pesanan tersebut.

[Hari 12: Midtrans Setup & Konfigurasi →](https://olipiskandar.gitbook.io/modul-pkl-xi-2025/minggu-3-checkout-and-payment/hari-12-midtrans-setup)
