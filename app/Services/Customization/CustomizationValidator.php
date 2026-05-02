<?php

namespace App\Services\Customization;

use App\Models\ProductTemplate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CustomizationValidator
{
    /**
     * Validate customization data against template rules.
     *
     * @param ProductTemplate $template
     * @param array $data
     * @throws ValidationException
     */
    public function validate(ProductTemplate $template, array $data): void
    {
        $rules = [];
        $messages = [];
        $editableAreas = collect($template->editable_areas ?? []);

        foreach ($editableAreas as $area) {
            $key = $area['key'] ?? null;
            if (!$key) continue;

            $type = $area['type'] ?? 'text';
            $label = $area['label'] ?? $key;
            $fieldRules = ['sometimes', 'nullable'];

            switch ($type) {
                case 'color':
                    $fieldRules[] = 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/';
                    $messages["{$key}.regex"] = "Le champ {$label} doit être un code couleur hexadécimal valide (ex: #FF0000).";
                    break;
                
                case 'text':
                    $fieldRules[] = 'string';
                    $fieldRules[] = 'max:500';
                    break;

                case 'image':
                    // If it's a string, it might be a URL or a path. 
                    // If it's an uploaded file, it should be handled by the controller first.
                    $fieldRules[] = 'string'; 
                    break;
                
                case 'font':
                    // Example: ensure it's in a list of allowed fonts if needed
                    $fieldRules[] = 'string';
                    $fieldRules[] = 'in:Arial,Verdana,Roboto,Oswald,Playfair Display,cursive';
                    break;
            }

            $rules[$key] = $fieldRules;
        }

        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
