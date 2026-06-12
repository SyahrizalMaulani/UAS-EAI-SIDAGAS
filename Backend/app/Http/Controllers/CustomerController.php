<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CustomerController extends Controller
{
    public function index()
    {
        // Endpoint API Gateway
        // Sesuaikan dengan URL API Gateway Anda. Jika Laravel jalan di host, gunakan localhost.
        // Jika Laravel jalan di dalam container satu network, gunakan http://api-gateway:3000
        $apiGatewayUrl = env('API_GATEWAY_URL', 'http://localhost:3000');

        // Query GraphQL untuk mengambil daftar produk (Inventory Service)
        $inventoryQuery = '
            query {
                getInventory {
                    id
                    item_name
                    stock
                }
            }
        ';

        // Query GraphQL untuk mengambil riwayat pesanan (Order Service)
        $orderQuery = '
            query {
                getOrders {
                    id
                    customer_name
                    item_name
                    quantity
                    status
                    created_at
                }
            }
        ';

        try {
            // Karena kita menggunakan microservices terpisah tanpa GraphQL Federation, 
            // kita panggil endpoint masing-masing melalui API Gateway
            
            // 1. Ambil Data Produk (Katalog)
            $responseInventory = Http::post($apiGatewayUrl . '/inventory', [
                'query' => $inventoryQuery
            ]);

            // 2. Ambil Data Riwayat Pesanan
            $responseOrder = Http::post($apiGatewayUrl . '/order', [
                'query' => $orderQuery
            ]);

            $daftarProduk = $responseInventory->json('data.getInventory') ?? [];
            $riwayatPesanan = $responseOrder->json('data.getOrders') ?? [];

            // Mempassing data ke view
            return view('user.dashboard', compact('daftarProduk', 'riwayatPesanan'));

        } catch (\Exception $e) {
            // Fallback jika API mati
            $daftarProduk = [];
            $riwayatPesanan = [];
            return view('user.dashboard', compact('daftarProduk', 'riwayatPesanan'))
                ->with('error', 'Gagal terhubung ke API Gateway: ' . $e->getMessage());
        }
    }
}
