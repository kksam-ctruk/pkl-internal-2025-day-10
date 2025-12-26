<?php
// ================================================================
// FILE: app/Http/Controllers/HomeController.php
// ================================================================
//
// TUJUAN:
// Controller ini menangani halaman BERANDA (Homepage) website.
// Halaman beranda adalah halaman pertama yang dilihat pengunjung
// saat membuka website. Harus menampilkan konten menarik yang
// mengundang visitor untuk explore lebih lanjut.
//
// KONTEN YANG DITAMPILKAN:
// 1. Hero section (banner utama) - biasanya berisi promo
// 2. Kategori populer - membantu navigasi cepathe
// 3. Produk unggulan (featured) - produk pilihan toko
// 4. Produk terbaru - menunjukkan toko aktif update
//
// ================================================================

namespace App\Http\Controllers;
// ↑ NAMESPACE: Alamat/lokasi class ini dalam struktur folder
//   App = folder app/
//   Http = folder Http/
//   Controllers = folder Controllers/
//   Jadi file ini ada di: app/Http/Controllers/HomeController.php

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
// ↑ USE STATEMENT: Mengimpor class yang akan digunakan
//   - Category: Model untuk tabel categories
//   - Product: Model untuk tabel products
//   - Request: Object yang berisi data HTTP request

class HomeController extends Controller
// ↑ CLASS DEFINITION:
//   - HomeController adalah nama class (harus sama dengan nama file)
//   - extends Controller: mewarisi fitur dari Controller dasar Laravel
//     (seperti middleware, response helpers, dll)
{
    /**
     * Menampilkan halaman beranda.
     *
     * @return \Illuminate\View\View
     */
    public function index()

    {

// ============================================================
// STEP 1: AMBIL DATA KATEGORI UNTUK SECTION "KATEGORI POPULER"
// ============================================================

$categories = Category::query()
    ->active()
    // Filter kategori yang punya minimal 1 produk aktif & ada stok
    ->whereHas('products', function($q) {
        $q->active()->inStock(); 
    })
    // Hitung jumlah produknya. 
    // Pakai alias 'products as active_products_count' supaya sinkron dengan view
    ->withCount(['products as active_products_count' => function($q) {
        $q->active()->inStock();
    }])
    // JANGAN pakai ->having() di sini agar tidak error di SQLite
    ->orderBy('name')
    ->take(6)
    ->get();



            $featuredProducts = Product::query()
        // ↑ Mulai query baru untuk tabel products

            ->with(['category', 'primaryImage'])
            // ↑ EAGER LOADING - SANGAT PENTING UNTUK PERFORMA!
            //
            //   MASALAH N+1 QUERY:
            //   Tanpa with(), jika kita punya 8 produk dan loop:
            //   @foreach($products as $p) {{ $p->category->name }} @endforeach
            //
            //   Akan terjadi:
            //   1 query ambil products
            //   + 8 query ambil category (1 per produk)
            //   + 8 query ambil image (1 per produk)
            //   = 17 query total!
            //
            //   DENGAN with():
            //   1 query: SELECT * FROM products WHERE ...
            //   1 query: SELECT * FROM categories WHERE id IN (1,2,3...)
            //   1 query: SELECT * FROM product_images WHERE product_id IN (1,2...) AND is_primary = 1
            //   = 3 query saja! Jauh lebih cepat!

            ->active()
            // ↑ Scope: WHERE is_active = true

            ->inStock()
            // ↑ Scope: WHERE stock > 0
            //   Produk yang stoknya habis tidak ditampilkan

            ->featured()
            // ↑ Scope: WHERE is_featured = true
            //   Produk yang di-flag featured oleh admin

            ->latest()
            // ↑ ORDER BY created_at DESC
            //   Tampilkan yang terbaru duluan

            ->take(8)
            // ↑ LIMIT 8 produk
            //   8 = 2 baris x 4 kolom di desktop

            ->get();
            // ↑ Eksekusi dan ambil hasil

        // ============================================================
        // STEP 3: AMBIL PRODUK TERBARU (LATEST PRODUCTS)
        // ============================================================

        $latestProducts = Product::query()
            ->with(['category', 'primaryImage'])
            ->active()
            ->inStock()
            // Tidak pakai ->featured() karena kita mau semua produk,
            // bukan hanya yang featured
            ->latest()
            // ↑ Urutkan dari yang paling baru
            ->take(8)
            ->get();

        // ============================================================
        // STEP 4: KIRIM DATA KE VIEW (BLADE)
        // ============================================================

        return view('home', compact(
            'categories',
            'featuredProducts',
            'latestProducts'
        ));
        // ↑ PENJELASAN:
        //
        //   view('home', [...]) artinya:
        //   - Cari file: resources/views/home.blade.php
        //   - Kirim data ke file tersebut
        //
        //   compact('categories', 'featuredProducts', 'latestProducts')
        //   adalah shortcut untuk:
        //   [
        //       'categories' => $categories,
        //       'featuredProducts' => $featuredProducts,
        //       'latestProducts' => $latestProducts,
        //   ]
        //
        //   Di dalam view, sekarang kita bisa akses:
        //   $categories, $featuredProducts, $latestProducts
    }
}