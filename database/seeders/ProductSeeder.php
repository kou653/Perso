<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductTemplate;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Mug Premium',
                'slug' => 'mug-premium',
                'description' => 'Mug personnalisable pour photo, texte ou logo.',
                'category' => 'mug',
                'base_image_url' => '/images/products/mug-premium.png',
                'templates' => [
                    [
                        'name' => 'Mug Photo Classique',
                        'slug' => 'mug-photo-classique',
                        'description' => 'Template mug avec photo centrale et texte court.',
                        'preview_data' => [
                            'background' => '#ffffff',
                            'style' => 'clean',
                            'mockup' => '/images/products/mug-premium.png',
                        ],
                        'layout' => [
                            'canvas' => ['width' => 2000, 'height' => 1200],
                            'zones' => [
                                'title' => ['x' => 180, 'y' => 820, 'width' => 700, 'height' => 160],
                                'image' => ['x' => 960, 'y' => 220, 'width' => 640, 'height' => 640],
                                'accent_color' => ['target' => 'background'],
                            ],
                        ],
                        'default_values' => [
                            'title' => 'Votre message ici',
                            'image' => null,
                            'accent_color' => '#d97706',
                        ],
                        'editable_areas' => [
                            ['key' => 'title', 'type' => 'text', 'label' => 'Texte principal', 'required' => true, 'max_length' => 40],
                            ['key' => 'image', 'type' => 'image', 'label' => 'Photo avant', 'required' => false],
                            ['key' => 'accent_color', 'type' => 'color', 'label' => 'Couleur dominante', 'required' => true],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'T-Shirt Street',
                'slug' => 't-shirt-street',
                'description' => 'T-shirt personnalisable avec visuel et slogan.',
                'category' => 't_shirt',
                'base_image_url' => '/images/products/tshirt-street.png',
                'templates' => [
                    [
                        'name' => 'T-Shirt Minimal Logo',
                        'slug' => 'tshirt-minimal-logo',
                        'description' => 'Template t-shirt avec logo poitrine et texte dos.',
                        'preview_data' => [
                            'background' => '#111111',
                            'style' => 'minimal',
                            'mockup' => '/images/products/tshirt-street.png',
                        ],
                        'layout' => [
                            'canvas' => ['width' => 1600, 'height' => 1800],
                            'zones' => [
                                'front_logo' => ['x' => 610, 'y' => 360, 'width' => 380, 'height' => 380],
                                'back_text' => ['x' => 300, 'y' => 1120, 'width' => 1000, 'height' => 180],
                                'shirt_color' => ['target' => 'garment'],
                            ],
                        ],
                        'default_values' => [
                            'front_logo' => null,
                            'back_text' => 'Create your move',
                            'shirt_color' => '#111111',
                        ],
                        'editable_areas' => [
                            ['key' => 'front_logo', 'type' => 'image', 'label' => 'Logo poitrine'],
                            ['key' => 'back_text', 'type' => 'text', 'label' => 'Texte au dos', 'max_length' => 60],
                            ['key' => 'shirt_color', 'type' => 'color', 'label' => 'Couleur du t-shirt'],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Sac Tote Urban',
                'slug' => 'sac-tote-urban',
                'description' => 'Sac personnalisable avec design textile et citation.',
                'category' => 'sac',
                'base_image_url' => '/images/products/sac-tote-urban.png',
                'templates' => [
                    [
                        'name' => 'Sac Citation Chic',
                        'slug' => 'sac-citation-chic',
                        'description' => 'Template sac avec texte central et motif simple.',
                        'preview_data' => [
                            'background' => '#eadcc8',
                            'style' => 'editorial',
                            'mockup' => '/images/products/sac-tote-urban.png',
                        ],
                        'layout' => [
                            'canvas' => ['width' => 1600, 'height' => 1800],
                            'zones' => [
                                'quote' => ['x' => 260, 'y' => 680, 'width' => 1080, 'height' => 240],
                                'accent_color' => ['target' => 'ink'],
                            ],
                        ],
                        'default_values' => [
                            'quote' => 'Le style commence ici',
                            'accent_color' => '#7c3aed',
                        ],
                        'editable_areas' => [
                            ['key' => 'quote', 'type' => 'text', 'label' => 'Citation', 'max_length' => 80],
                            ['key' => 'accent_color', 'type' => 'color', 'label' => 'Couleur accent'],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Porte-Cle Metal',
                'slug' => 'porte-cle-metal',
                'description' => 'Porte-cle personnalise avec nom, date ou symbole.',
                'category' => 'porte_cle',
                'base_image_url' => '/images/products/porte-cle-metal.png',
                'templates' => [
                    [
                        'name' => 'Porte-Cle Initiales',
                        'slug' => 'porte-cle-initiales',
                        'description' => 'Template porte-cle gravure simple.',
                        'preview_data' => [
                            'background' => '#b6bcc8',
                            'style' => 'engraved',
                            'mockup' => '/images/products/porte-cle-metal.png',
                        ],
                        'layout' => [
                            'canvas' => ['width' => 900, 'height' => 900],
                            'zones' => [
                                'initials' => ['x' => 220, 'y' => 350, 'width' => 460, 'height' => 160],
                            ],
                        ],
                        'default_values' => [
                            'initials' => 'AB',
                        ],
                        'editable_areas' => [
                            ['key' => 'initials', 'type' => 'text', 'label' => 'Initiales', 'max_length' => 6],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Stylo Signature',
                'slug' => 'stylo-signature',
                'description' => 'Stylo personnalisable pour cadeaux et branding.',
                'category' => 'stylo',
                'base_image_url' => '/images/products/stylo-signature.png',
                'templates' => [
                    [
                        'name' => 'Stylo Corporate',
                        'slug' => 'stylo-corporate',
                        'description' => 'Template stylo avec nom ou marque.',
                        'preview_data' => [
                            'background' => '#0f172a',
                            'style' => 'corporate',
                            'mockup' => '/images/products/stylo-signature.png',
                        ],
                        'layout' => [
                            'canvas' => ['width' => 1800, 'height' => 500],
                            'zones' => [
                                'brand_name' => ['x' => 420, 'y' => 180, 'width' => 920, 'height' => 120],
                                'body_color' => ['target' => 'body'],
                            ],
                        ],
                        'default_values' => [
                            'brand_name' => 'Votre marque',
                            'body_color' => '#0f172a',
                        ],
                        'editable_areas' => [
                            ['key' => 'brand_name', 'type' => 'text', 'label' => 'Nom de marque', 'max_length' => 30],
                            ['key' => 'body_color', 'type' => 'color', 'label' => 'Couleur du stylo'],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Casquette Sport',
                'slug' => 'casquette-sport',
                'description' => 'Casquette personnalisable avec patch, logo et texte.',
                'category' => 'casquette',
                'base_image_url' => '/images/products/casquette-sport.png',
                'templates' => [
                    [
                        'name' => 'Casquette Club',
                        'slug' => 'casquette-club',
                        'description' => 'Template casquette avec logo frontal.',
                        'preview_data' => [
                            'background' => '#dc2626',
                            'style' => 'sport',
                            'mockup' => '/images/products/casquette-sport.png',
                        ],
                        'layout' => [
                            'canvas' => ['width' => 1600, 'height' => 1200],
                            'zones' => [
                                'front_patch' => ['x' => 520, 'y' => 360, 'width' => 540, 'height' => 320],
                                'side_text' => ['x' => 1100, 'y' => 700, 'width' => 260, 'height' => 120],
                                'cap_color' => ['target' => 'fabric'],
                            ],
                        ],
                        'default_values' => [
                            'front_patch' => null,
                            'side_text' => 'CLUB',
                            'cap_color' => '#dc2626',
                        ],
                        'editable_areas' => [
                            ['key' => 'front_patch', 'type' => 'image', 'label' => 'Patch frontal'],
                            ['key' => 'side_text', 'type' => 'text', 'label' => 'Texte lateral', 'max_length' => 20],
                            ['key' => 'cap_color', 'type' => 'color', 'label' => 'Couleur de la casquette'],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($products as $productData) {
            $templates = $productData['templates'];
            unset($productData['templates']);

            $product = Product::query()->updateOrCreate(
                ['slug' => $productData['slug']],
                $productData,
            );

            foreach ($templates as $templateData) {
                ProductTemplate::query()->updateOrCreate(
                    ['slug' => $templateData['slug']],
                    [...$templateData, 'product_id' => $product->id],
                );
            }
        }
    }
}
