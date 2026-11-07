<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use App\Models\StockIn;
use App\Models\StockInItem;
use App\Models\StockMovement;
use App\Models\StockOut;
use App\Models\StockOutItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StockService
{
    /**
     * Simpan transaksi barang masuk beserta item dan kartu stoknya dalam satu transaksi database.
     */
    public function createStockIn(array $data, User $user): StockIn
    {
        $items = $data['items'] ?? [];
        if (empty($items)) {
            throw new InvalidArgumentException('Transaksi minimal harus memiliki satu baris barang.');
        }

        return DB::transaction(function () use ($data, $items, $user): StockIn {
            $tanggal = Carbon::parse($data['tanggal']);
            $timestamp = isset($data['created_at'])
                ? Carbon::parse($data['created_at'])
                : ($tanggal->isToday() ? now() : $tanggal->copy()->setTime(9, 0));

            // Kunci baris barang berurutan menurut id menaik untuk mencegah deadlock (PRD §11.3)
            $productIds = collect($items)->pluck('product_id')->map(fn ($id) => (int) $id)->unique()->sort()->values();
            if ($productIds->count() !== count($items)) {
                throw new InvalidArgumentException('Barang yang sama tidak boleh dimasukkan dua kali. Gabungkan jumlahnya.');
            }

            $products = Product::whereIn('id', $productIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $stockIn = $this->createHeaderWithRetry('BM', $tanggal, function (string $noTransaksi) use ($data, $tanggal, $user, $timestamp): StockIn {
                $header = new StockIn([
                    'no_transaksi' => $noTransaksi,
                    'tanggal' => $tanggal->toDateString(),
                    'supplier_id' => $data['supplier_id'] ?? null,
                    'user_id' => $user->id,
                    'keterangan' => $data['keterangan'] ?? null,
                ]);
                $header->created_at = $timestamp;
                $header->updated_at = $timestamp;
                $header->save();

                return $header;
            });

            foreach ($items as $item) {
                $productId = (int) $item['product_id'];
                $qty = (int) $item['jumlah'];

                if ($qty <= 0) {
                    throw new InvalidArgumentException('Jumlah harus bilangan bulat lebih dari 0.');
                }

                /** @var Product|null $product */
                $product = $products->get($productId);
                if (! $product) {
                    throw new InvalidArgumentException('Barang tidak ditemukan.');
                }

                // Jika harga beli item dikosongkan, gunakan harga beli referensi barang (PRD §7.6)
                $hargaBeli = isset($item['harga_beli']) && $item['harga_beli'] !== ''
                    ? (float) $item['harga_beli']
                    : (float) $product->harga_beli;

                StockInItem::create([
                    'stock_in_id' => $stockIn->id,
                    'product_id' => $product->id,
                    'jumlah' => $qty,
                    'harga_beli' => $hargaBeli,
                ]);

                $this->increase($product, $qty, $stockIn, $user, $timestamp);
            }

            return $stockIn->load(['supplier', 'user', 'items.product.unit']);
        });
    }

    /**
     * Simpan transaksi barang keluar dengan pengecekan stok ber-lock dan pencatatan kartu stok.
     */
    public function createStockOut(array $data, User $user): StockOut
    {
        $items = $data['items'] ?? [];
        if (empty($items)) {
            throw new InvalidArgumentException('Transaksi minimal harus memiliki satu baris barang.');
        }

        return DB::transaction(function () use ($data, $items, $user): StockOut {
            $tanggal = Carbon::parse($data['tanggal']);
            $timestamp = isset($data['created_at'])
                ? Carbon::parse($data['created_at'])
                : ($tanggal->isToday() ? now() : $tanggal->copy()->setTime(14, 0));

            // Kunci baris barang berurutan menurut id menaik untuk mencegah deadlock & race condition (PRD §9.6, §11.3)
            $productIds = collect($items)->pluck('product_id')->map(fn ($id) => (int) $id)->unique()->sort()->values();
            if ($productIds->count() !== count($items)) {
                throw new InvalidArgumentException('Barang yang sama tidak boleh dimasukkan dua kali. Gabungkan jumlahnya.');
            }

            $products = Product::whereIn('id', $productIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // Validasi awal seluruh item sebelum membuat header agar segera gagal bila stok kurang
            foreach ($items as $item) {
                $productId = (int) $item['product_id'];
                $qty = (int) $item['jumlah'];

                if ($qty <= 0) {
                    throw new InvalidArgumentException('Jumlah harus bilangan bulat lebih dari 0.');
                }

                /** @var Product|null $product */
                $product = $products->get($productId);
                if (! $product) {
                    throw new InvalidArgumentException('Barang tidak ditemukan.');
                }
            }

            $stockOut = $this->createHeaderWithRetry('BK', $tanggal, function (string $noTransaksi) use ($data, $tanggal, $user, $timestamp): StockOut {
                $header = new StockOut([
                    'no_transaksi' => $noTransaksi,
                    'tanggal' => $tanggal->toDateString(),
                    'tujuan' => $data['tujuan'] ?? null,
                    'alasan' => $data['alasan'],
                    'user_id' => $user->id,
                    'keterangan' => $data['keterangan'] ?? null,
                ]);
                $header->created_at = $timestamp;
                $header->updated_at = $timestamp;
                $header->save();

                return $header;
            });

            foreach ($items as $item) {
                $product = $products->get((int) $item['product_id']);
                $qty = (int) $item['jumlah'];

                StockOutItem::create([
                    'stock_out_id' => $stockOut->id,
                    'product_id' => $product->id,
                    'jumlah' => $qty,
                ]);

                // decrease() memeriksa ulang stok dan melempar InsufficientStockException (memicu rollback seluruh transaksi)
                $this->decrease($product, $qty, $stockOut, $user, $timestamp);
            }

            return $stockOut->load(['user', 'items.product.unit']);
        });
    }

    /**
     * Tambah stok barang dan tulis satu baris kartu stok bertipe IN (PRD §11.3, BR-04, BR-05).
     */
    public function increase(Product $product, int $qty, Model $ref, User $user, ?Carbon $timestamp = null): void
    {
        if ($qty <= 0) {
            throw new InvalidArgumentException('Jumlah harus bilangan bulat lebih dari 0.');
        }

        $sebelum = $product->stok;
        $product->stok = $sebelum + $qty;
        $product->save();

        StockMovement::create([
            'product_id' => $product->id,
            'tipe' => 'in',
            'referensi_tipe' => 'stock_in',
            'referensi_id' => $ref->id,
            'jumlah' => $qty,
            'stok_sebelum' => $sebelum,
            'stok_sesudah' => $product->stok,
            'user_id' => $user->id,
            'created_at' => $timestamp ?? now(),
        ]);
    }

    /**
     * Kurangi stok barang dan tulis satu baris kartu stok bertipe OUT; tolak bila stok tidak cukup (PRD §11.3, BR-01).
     */
    public function decrease(Product $product, int $qty, Model $ref, User $user, ?Carbon $timestamp = null): void
    {
        if ($qty <= 0) {
            throw new InvalidArgumentException('Jumlah harus bilangan bulat lebih dari 0.');
        }

        if ($product->stok < $qty) {
            throw new InsufficientStockException($product, $qty);
        }

        $sebelum = $product->stok;
        $product->stok = $sebelum - $qty;
        $product->save();

        StockMovement::create([
            'product_id' => $product->id,
            'tipe' => 'out',
            'referensi_tipe' => 'stock_out',
            'referensi_id' => $ref->id,
            'jumlah' => $qty,
            'stok_sebelum' => $sebelum,
            'stok_sesudah' => $product->stok,
            'user_id' => $user->id,
            'created_at' => $timestamp ?? now(),
        ]);
    }

    /**
     * Buat nomor transaksi harian berurutan: {BM|BK}-{YYYYMMDD}-{urut 3 digit, melebar ke 4 digit bila > 999} (PRD §11.3).
     */
    public function generateNumber(string $prefix, Carbon $date): string
    {
        $prefix = strtoupper($prefix);
        $datePart = $date->format('Ymd');
        $basePrefix = "{$prefix}-{$datePart}-";

        $query = $prefix === 'BM'
            ? StockIn::where('no_transaksi', 'like', "{$basePrefix}%")
            : StockOut::where('no_transaksi', 'like', "{$basePrefix}%");

        $existingNumbers = $query->lockForUpdate()->pluck('no_transaksi');

        $maxSequence = 0;
        foreach ($existingNumbers as $no) {
            $suffix = substr((string) $no, strlen($basePrefix));
            if (ctype_digit($suffix)) {
                $seq = (int) $suffix;
                if ($seq > $maxSequence) {
                    $maxSequence = $seq;
                }
            }
        }

        $nextSequence = $maxSequence + 1;
        $padLength = $nextSequence > 999 ? 4 : 3;

        return $basePrefix.str_pad((string) $nextSequence, $padLength, '0', STR_PAD_LEFT);
    }

    /**
     * Buat header transaksi dengan percobaan ulang hingga 3 kali bila terjadi tabrakan nomor unik (PRD §9.11, §11.3).
     *
     * @template TModel of Model
     *
     * @param  callable(string): TModel  $callback
     * @return TModel
     */
    protected function createHeaderWithRetry(string $prefix, Carbon $date, callable $callback): Model
    {
        $attempts = 0;

        while (true) {
            $attempts++;
            $noTransaksi = $this->generateNumber($prefix, $date);

            try {
                return $callback($noTransaksi);
            } catch (QueryException $e) {
                if ($attempts >= 3) {
                    throw $e;
                }
            }
        }
    }
}

