<?php

namespace App\Services\Customization;

use App\Models\ProductTemplate;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class TemplateRenderer
{
    public function render(ProductTemplate $template, array $customization = []): array
    {
        $areas = collect($template->editable_areas ?? []);
        $allowedKeys = $areas->pluck('key')->filter()->values()->all();
        $unknownKeys = array_values(array_diff(array_keys($customization), $allowedKeys));

        if ($unknownKeys !== []) {
            throw ValidationException::withMessages([
                'customization_data' => [
                    'Unknown customization keys: '.implode(', ', $unknownKeys),
                ],
            ]);
        }

        $resolvedValues = array_merge($template->default_values ?? [], $customization);

        $resolvedAreas = $areas->map(function (array $area) use ($resolvedValues): array {
            $key = $area['key'] ?? null;

            return [
                ...$area,
                'value' => $key ? Arr::get($resolvedValues, $key) : null,
            ];
        })->all();

        return [
            'template' => [
                'id' => $template->id,
                'slug' => $template->slug,
                'name' => $template->name,
            ],
            'preview' => $template->preview_data ?? [],
            'layout' => $template->layout ?? [],
            'values' => $resolvedValues,
            'areas' => $resolvedAreas,
        ];
    }
}
