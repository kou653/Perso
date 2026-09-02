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
                'name' => 'Mug Céramique Personnalisé',
                'slug' => 'mug',
                'description' => 'Mug en céramique de haute qualité (330ml), résistant au micro-ondes et lave-vaisselle.',
                'category' => 'mug',
                'base_image_url' => '/model/mug-maman-damour.jpg',
                'templates' => [
                    [
                        'name' => 'Cadeau Belle-Mère & Famille',
                        'slug' => 'mug-belle-mere',
                        'description' => 'Tasse avec poignée en cœur, collage de 2 photos polaroid et message d\'affection.',
                        'preview_data' => [
                            'imageUrl' => '/model/mug-belle-mere.jpg',
                            'category' => 'Cadeau & Famille',
                        ],
                        'layout' => [],
                        'default_values' => [
                            'header_text' => 'Dear mother-in-law',
                            'photo_1' => '/model/mug-belle-mere.jpg',
                            'photo_2' => '/model/mug-belle-mere.jpg',
                            'footer_message' => 'Thank you my mother-in-law for treating me like a daughter.',
                        ],
                        'editable_areas' => [
                            ['key' => 'header_text', 'type' => 'text', 'label' => 'Titre d\'en-tête', 'required' => false],
                            ['key' => 'photo_1', 'type' => 'image', 'label' => 'Photo Polaroid 1 (gauche)', 'required' => false],
                            ['key' => 'photo_2', 'type' => 'image', 'label' => 'Photo Polaroid 2 (droite)', 'required' => false],
                            ['key' => 'footer_message', 'type' => 'text', 'label' => 'Message de remerciement', 'required' => false],
                        ],
                    ],
                    [
                        'name' => 'Calendrier Date Spéciale & Photo',
                        'slug' => 'mug-calendrier-couple',
                        'description' => 'Double face : calendrier avec cœur sur la date clé et photo de couple avec mot doux.',
                        'preview_data' => [
                            'imageUrl' => '/model/mug-calendrier-couple.jpg',
                            'category' => 'Amour & Couple',
                        ],
                        'layout' => [],
                        'default_values' => [
                            'date_title' => '06 de Agosto de 2022',
                            'day_number' => '6',
                            'bottom_left_text' => 'quando tudo começou...',
                            'photo_couple' => '/model/mug-calendrier-couple.jpg',
                            'couple_message' => 'Te amo !!',
                        ],
                        'editable_areas' => [
                            ['key' => 'date_title', 'type' => 'text', 'label' => 'Mois et Année du calendrier', 'required' => false],
                            ['key' => 'day_number', 'type' => 'text', 'label' => 'Numéro du jour entouré d\'un cœur', 'required' => false],
                            ['key' => 'bottom_left_text', 'type' => 'text', 'label' => 'Citation sous calendrier', 'required' => false],
                            ['key' => 'photo_couple', 'type' => 'image', 'label' => 'Photo de couple', 'required' => false],
                            ['key' => 'couple_message', 'type' => 'text', 'label' => 'Message d\'amour', 'required' => false],
                        ],
                    ],
                    [
                        'name' => 'Best Mom Ever & Poème',
                        'slug' => 'mug-magique-maman',
                        'description' => 'Face avant avec photo et mention \'Best Mom Ever\', face arrière avec un poème émouvant.',
                        'preview_data' => [
                            'imageUrl' => '/model/mug-magique-maman.png',
                            'category' => 'Famille & Maman',
                        ],
                        'layout' => [],
                        'default_values' => [
                            'front_title' => 'Best Mom Ever',
                            'mom_photo' => '/model/mug-magique-maman.png',
                            'front_subtitle' => 'WE LOVE YOU SO MUCH',
                            'back_poem' => 'Tu es notre repère, notre force et notre plus belle histoire ❤️',
                        ],
                        'editable_areas' => [
                            ['key' => 'front_title', 'type' => 'text', 'label' => 'Titre face avant', 'required' => false],
                            ['key' => 'mom_photo', 'type' => 'image', 'label' => 'Photo maman & enfant(s)', 'required' => false],
                            ['key' => 'front_subtitle', 'type' => 'text', 'label' => 'Sous-titre face avant', 'required' => false],
                            ['key' => 'back_poem', 'type' => 'text', 'label' => 'Poème / Message face arrière', 'required' => false],
                        ],
                    ],
                    [
                        'name' => 'Monogramme Doré Élégant',
                        'slug' => 'mug-monogramme-or',
                        'description' => 'Grande initiale dorée majuscule ornée d\'un prénom calligraphié en noir.',
                        'preview_data' => [
                            'imageUrl' => '/model/mug-monogramme-or.jpg',
                            'category' => 'Monogramme & Élégant',
                        ],
                        'layout' => [],
                        'default_values' => [
                            'initial' => 'M',
                            'name' => 'Mackenzie',
                        ],
                        'editable_areas' => [
                            ['key' => 'initial', 'type' => 'text', 'label' => 'Initiale Monogramme (Doré)', 'required' => false],
                            ['key' => 'name', 'type' => 'text', 'label' => 'Prénom calligraphié', 'required' => false],
                        ],
                    ],
                    [
                        'name' => 'Maman d\'Amour & Prénoms',
                        'slug' => 'mug-maman-damour',
                        'description' => 'Mug avec intérieur rouge. Face 1 : \'Maman D\'AMOUR\', Face 2 : Prénoms des enfants.',
                        'preview_data' => [
                            'imageUrl' => '/model/mug-maman-damour.jpg',
                            'category' => 'Famille & Maman',
                        ],
                        'layout' => [],
                        'default_values' => [
                            'title' => 'Maman D\'AMOUR',
                            'children_names' => "Alma, Lucie, Noah",
                        ],
                        'editable_areas' => [
                            ['key' => 'title', 'type' => 'text', 'label' => 'Titre face 1', 'required' => false],
                            ['key' => 'children_names', 'type' => 'text', 'label' => 'Prénoms des enfants (face 2)', 'required' => false],
                        ],
                    ],
                    [
                        'name' => 'Tu es l\'amour de ma vie',
                        'slug' => 'mug-ourson-amour',
                        'description' => 'Mug intérieur rouge avec la déclaration d\'amour et le prénom personnalisé.',
                        'preview_data' => [
                            'imageUrl' => '/model/mug-ourson-amour.jpg',
                            'category' => 'Amour & Saint-Valentin',
                        ],
                        'layout' => [],
                        'default_values' => [
                            'quote' => 'Tu es L\'AMOUR de ma vie',
                            'name' => 'Caroline',
                        ],
                        'editable_areas' => [
                            ['key' => 'quote', 'type' => 'text', 'label' => 'Message / Déclaration', 'required' => false],
                            ['key' => 'name', 'type' => 'text', 'label' => 'Prénom personnalisé', 'required' => false],
                        ],
                    ],
                    [
                        'name' => 'Photo Souvenir, Noms & Date',
                        'slug' => 'mug-photo-souvenir',
                        'description' => 'Photo carrée haute définition, prénoms de couple élégants et date commémorative.',
                        'preview_data' => [
                            'imageUrl' => '/model/mug-photo-souvenir.jpg',
                            'category' => 'Photo & Souvenir',
                        ],
                        'layout' => [],
                        'default_values' => [
                            'photo' => '/model/mug-photo-souvenir.jpg',
                            'couple_names' => 'Lucas & Isabella',
                            'special_date' => '22.06.2024',
                        ],
                        'editable_areas' => [
                            ['key' => 'photo', 'type' => 'image', 'label' => 'Photo principale', 'required' => false],
                            ['key' => 'couple_names', 'type' => 'text', 'label' => 'Noms / Prénoms', 'required' => false],
                            ['key' => 'special_date', 'type' => 'text', 'label' => 'Date mémorable', 'required' => false],
                        ],
                    ],
                    [
                        'name' => 'Prénom Minimaliste & Cœur',
                        'slug' => 'mug-minimaliste-prenom',
                        'description' => 'Design épuré et raffiné avec un cœur délicat et votre prénom manuscrit.',
                        'preview_data' => [
                            'imageUrl' => '/model/mug-minimaliste-prenom.jpg',
                            'category' => 'Minimaliste & Prénom',
                        ],
                        'layout' => [],
                        'default_values' => [
                            'name' => 'Emma',
                        ],
                        'editable_areas' => [
                            ['key' => 'name', 'type' => 'text', 'label' => 'Prénom personnalisé', 'required' => false],
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
                    [...$templateData, 'product_id' => $product->id, 'is_active' => true],
                );
            }
        }
    }
}

