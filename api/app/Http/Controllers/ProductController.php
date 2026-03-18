<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Auth;
use DB;

class ProductController
{
    public function index(Request $request)
    {
        $query = Product::with('images'); // Carrega as imagens junto

        if ($request->has('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        if ($request->filled('active')) {
            $query->where('active', $request->active === 'true');
        }

        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Ordenação (padrão: mais recentes primeiro)
        $query->orderBy('created_at', 'desc');

        // Paginação (10 por página)
        $products = $query->paginate(10);

        return Response::success($products, 'Produtos listados com sucesso');
    }

    public function show($id)
    {
        try {
            $product = Product::with(['images'])->findOrFail($id);

            return Response::success($product, 'Produto encontrado com sucesso');

        } catch (ModelNotFoundException $e) {
            return Response::error('Produto não encontrado', 404);

        } catch (\Exception $e) {
            \Log::error('Erro ao buscar produto: ' . $e->getMessage());
            return Response::error('Erro ao buscar produto', 500);
        }
    }

    public function store(ProductRequest $request)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            // Limpar HTML (apenas tags permitidas)
            $description = strip_tags($data['description'], '<p><br><b><strong>');

            // Preço >= Custo + 10%
            $cost = $request->cost;
            $price = $request->price;

            if ($price < ($cost * 1.10)) {
                return Response::error('O preço deve ser pelo menos 10% maior que o custo.', 422);
            }

            // Criar produto
            $product = Product::create([
                'title' => $request->title,
                'cost' => $request->cost,
                'price' => $request->price,
                'description' => $description,
                'active' => $request->active ?? true,
            ]);

            // Upload de imagens
            if ($request->hasFile('images')) {

                $data = [];

                foreach ($request->file('images') as $index => $image) {
                    $path = $image->store('products', 'public');

                    $data[] = [
                        'product_id' => $product->id,
                        'image_path' => $path,
                        'order' => $index + 1,
                    ];
                }

                ProductImage::insert($data);
            }

            DB::commit();

            $product->load('images');

            return Response::success($product, 'Produto criado com sucesso', 201);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Erro ao criar produto: ' . $e->getMessage());
            return Response::error('Erro ao criar produto', 500);
        }
    }

    public function update(ProductRequest $request, $id)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            $product = Product::findOrFail($id);

            // Limpar HTML (apenas tags permitidas)
            $description = strip_tags($data['description'], '<p><br><b><strong>');

            // Preço >= Custo + 10%
            $cost = $data['cost'] ?? $product->cost;
            $price = $data['price'] ?? $product->price;

            if ($price < ($cost * 1.10)) {
                return Response::error('O preço deve ser pelo menos 10% maior que o custo.', 422);
            }

            // Atualizar produto
            $product->update([
                'title' => $data['title'] ?? $product->title,
                'cost' => $data['cost'] ?? $product->cost,
                'price' => $data['price'] ?? $product->price,
                'description' => $description,
                'active' => $data['active'] ?? $product->active,
                'updated_by' => Auth::id(),
            ]);

            // Upload de imagens
            if ($request->hasFile('images')) {

                $data = [];

                foreach ($request->file('images') as $index => $image) {
                    $path = $image->store('products', 'public');

                    $data[] = [
                        'product_id' => $product->id,
                        'image_path' => $path,
                        'order' => $index + 1,
                    ];
                }

                ProductImage::insert($data);
            }

            DB::commit();

            return Response::success($product, 'Produto atualizado com sucesso');

        } catch (ModelNotFoundException $e) {
            return Response::error('Produto não encontrado.', 404);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Erro ao atualizar produto: ' . $e->getMessage());
            return Response::error('Erro ao atualizar produto.', 500);
        }
    }

    public function desactive($id)
    {
        try {
            $product = Product::findOrFail($id);

            $product->update(['active' => false]);

            return Response::success($product, 'Produto desativado com sucesso');

        } catch (ModelNotFoundException $e) {
            return Response::error('Produto não encontrado', 404);

        } catch (\Exception $e) {
            \Log::error('Erro ao desativar produto: ' . $e->getMessage());
            return Response::error('Erro ao desativar produto', 500);
        }
    }

    public function activate($id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->update(['active' => true]);

            return Response::success($product, 'Produto ativado com sucesso');

        } catch (ModelNotFoundException $e) {
            return Response::error('Produto não encontrado', 404);

        } catch (\Exception $e) {
            \Log::error('Erro ao ativar produto: ' . $e->getMessage());
            return Response::error('Erro ao ativar produto', 500);
        }
    }

    public function removeImage($productId, $imageId)
    {
        try {
            // Busca o produto
            $product = Product::findOrFail($productId);

            // Busca a imagem e verifica se pertence ao produto (segurança)
            $image = ProductImage::where('id', $imageId)
                ->where('product_id', $product->id)
                ->firstOrFail();

            $imagePath = $image->image_path;

            // Deleta o arquivo físico do storage
            if (Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }

            // Deleta o registro do banco
            $image->delete();

            return Response::success(true, 'Imagem removida com sucesso');

        } catch (ModelNotFoundException $e) {
            return Response::error('Produto não encontrado', 404);

        } catch (\Exception $e) {
            \Log::error('Erro ao remover imagem: ' . $e->getMessage());
            return Response::error('Erro ao remover imagem.', 500);
        }
    }

}
