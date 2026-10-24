<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProdukRequest;
use App\Http\Requests\UpdateProdukRequest;
use App\Http\Resources\ProdukResource;
use App\Models\Produk;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class ProdukController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'merek' => ['nullable', 'string', 'max:255'],
            'urut' => ['nullable', 'in:terbaru,harga_asc,harga_desc'],
        ]);

        $query = Produk::query()->where('is_aktif', true);
        $pencarian = trim((string) ($filters['q'] ?? ''));

        if ($pencarian !== '') {
            $query->where(function ($subquery) use ($pencarian) {
                $subquery->where('nama', 'like', "%{$pencarian}%")
                    ->orWhere('merek', 'like', "%{$pencarian}%");
            });
        }

        if (! empty($filters['merek'])) {
            $query->where('merek', $filters['merek']);
        }

        match ($filters['urut'] ?? 'terbaru') {
            'harga_asc' => $query->orderBy('harga')->orderBy('id'),
            'harga_desc' => $query->orderByDesc('harga')->orderByDesc('id'),
            default => $query->orderByDesc('created_at')->orderByDesc('id'),
        };

        return ProdukResource::collection($query->get());
    }

    public function show(string $produk): ProdukResource
    {
        $item = Produk::query()
            ->where('is_aktif', true)
            ->findOrFail($produk);

        return new ProdukResource($item);
    }

    public function merek(): JsonResponse
    {
        $merek = Produk::query()
            ->where('is_aktif', true)
            ->distinct()
            ->orderBy('merek')
            ->pluck('merek')
            ->values();

        return response()->json([
            'data' => $merek,
        ]);
    }

    public function adminIndex(): AnonymousResourceCollection
    {
        return ProdukResource::collection(
            Produk::query()->orderByDesc('id')->get()
        );
    }

    public function store(StoreProdukRequest $request): JsonResponse
    {
        $produk = Produk::query()->create($request->validated());

        return (new ProdukResource($produk))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(UpdateProdukRequest $request, Produk $produk): ProdukResource
    {
        $produk->update($request->validated());

        return new ProdukResource($produk);
    }

    public function destroy(Produk $produk): Response
    {
        $produk->delete();

        return response()->noContent();
    }
}
