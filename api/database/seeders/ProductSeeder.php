<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Produtos Fixos - Linha de Caixões
        Product::create([
            'title' => 'Caixão Luxo Madeira Nobre',
            'description' => '<p>Caixão confeccionado em madeira nobre maciça.</p><br><strong>Acabamento premium com detalhes em veludo.</strong></p>',
            'cost' => 1200.00,
            'price' => 1890.00,
            'active' => true,
        ]);

        Product::create([
            'title' => 'Caixão Simples Ecônomico',
            'description' => '<p>Opção acessível e digna.</p><br><p>Madeira reflorestada com acabamento laminado.</p>',
            'cost' => 350.00,
            'price' => 550.00,
            'active' => true,
        ]);

        Product::create([
            'title' => 'Caixão Infantil Anjo',
            'description' => '<p>Design especial e delicado para crianças.</p><br><strong>Acabamento em cetim branco.</strong></p>',
            'cost' => 600.00,
            'price' => 890.00,
            'active' => true,
        ]);

        Product::create([
            'title' => 'Caixão Hermetico VIP',
            'description' => '<p>Vedação completa com sistema de preservação.</p><br><p>Ideal para traslados de longa distância.</p>',
            'cost' => 2500.00,
            'price' => 3900.00,
            'active' => true,
        ]);

        Product::create([
            'title' => 'Caixão Ecológico Biodegradável',
            'description' => '<p>Feito com fibras naturais.</p><br><strong>Respeita o meio ambiente.</strong></p>',
            'cost' => 800.00,
            'price' => 1200.00,
            'active' => true,
        ]);

        Product::create([
            'title' => 'Urna Funerária Clássica',
            'description' => '<p>Urna para cinzas em material resistente.</p><br><p>Design sóbrio e elegante.</p>',
            'cost' => 150.00,
            'price' => 250.00,
            'active' => true,
        ]);

        Product::create([
            'title' => 'Urna Decorativa Premium',
            'description' => '<p>Acabamento em bronze com gravações personalizadas.</p><br><strong>Peça de colecionador.</strong></p>',
            'cost' => 400.00,
            'price' => 650.00,
            'active' => true,
        ]);

        Product::create([
            'title' => 'Coroa de Flores Natural',
            'description' => '<p>Arranjo floral fresco com rosas e lírios.</p><br><p>Diâmetro de 60cm.</p>',
            'cost' => 120.00,
            'price' => 220.00,
            'active' => true,
        ]);

        Product::create([
            'title' => 'Livro de Condolências',
            'description' => '<p>Registro para mensagens de pesar.</p><br><strong>Capa em couro sintético.</strong></p>',
            'cost' => 25.00,
            'price' => 45.00,
            'active' => true,
        ]);

        Product::create([
            'title' => 'Velas Aromáticas Cerimonial',
            'description' => '<p>Kit com 12 velas para cerimônia.</p><br><p>Duração de 8 horas cada.</p>',
            'cost' => 30.00,
            'price' => 55.00,
            'active' => false,
        ]);
    }
}
