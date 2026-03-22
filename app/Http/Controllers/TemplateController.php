<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Template;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    public function index()
    {
        $templates = Template::with('service')->latest()->get();

        return view('templates.index', compact('templates'));
    }

    public function create()
    {
        $services = Service::orderBy('nombre')->get();

        return view('templates.create', compact('services'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'service_id' => 'required|exists:services,id',
            'nombre_plantilla' => 'required|string|max:255',
            'html_content' => 'required|string',
            'fields_schema' => 'nullable|string',
        ]);

        $data['fields_schema'] = $this->normalizeFieldsSchema($data['fields_schema'] ?? null);

        Template::create($data);

        return redirect()->route('templates.index')->with('success', 'Plantilla creada con éxito.');
    }

    public function edit($id)
    {
        $template = Template::findOrFail($id);
        $services = Service::orderBy('nombre')->get();

        return view('templates.edit', compact('template', 'services'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'service_id' => 'required|exists:services,id',
            'nombre_plantilla' => 'required|string|max:255',
            'html_content' => 'required|string',
            'fields_schema' => 'nullable|string',
        ]);

        $data['fields_schema'] = $this->normalizeFieldsSchema($data['fields_schema'] ?? null);

        Template::findOrFail($id)->update($data);

        return redirect()->route('templates.index')->with('success', 'Plantilla actualizada.');
    }

    public function destroy($id)
    {
        try {
            Template::findOrFail($id)->delete();

            return response()->json(['success' => true]);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar la plantilla porque tiene registros asociados.',
            ], 409);
        }
    }

    public function preview(Template $template)
    {
        $sexo = 'M';
        $data = [
            '{{nombre_paciente}}' => 'JUAN PÉREZ GARCÍA',
            '{{dni_paciente}}' => '78945612',
            '{{edad_paciente}}' => '32 AÑOS',
            '{{sexo_paciente}}' => $sexo,
            '{{fecha_actual}}' => now()->format('d/m/Y'),
            '{{codigo_orden}}' => 'ORD-2026-0001',
            '{{regimen_aseguramiento}}' => 'SIS',
            '{{codigo_afiliacion}}' => 'AFI-11223344',
            '{{firma_medico}}' => '<div style="text-align:center;margin-top:30px;"><div style="border-top:1px solid #000;width:260px;margin:0 auto 8px auto;"></div><div style="font-weight:bold;">NOMBRE DEL PROFESIONAL</div><div>MÉDICO</div><div>COL. 00000</div></div>',
        ];

        $htmlPrevisualizado = $this->applyConditionalBlocks($template->html_content, $sexo);
        $htmlPrevisualizado = str_replace(array_keys($data), array_values($data), $htmlPrevisualizado);

        foreach ($template->fields_schema ?? [] as $field) {
            $key = trim((string) data_get($field, 'key'));
            if (!$key) {
                continue;
            }

            $htmlPrevisualizado = str_replace('{{campo:' . $key . '}}', '[' . data_get($field, 'label', $key) . ']', $htmlPrevisualizado);
        }

        return view('templates.preview', compact('template', 'htmlPrevisualizado'));
    }

    public function render(Template $template)
    {
        $fieldsSchema = collect($template->fields_schema ?? [])->values()->all();

        return view('templates.render', compact('template', 'fieldsSchema'));
    }

    private function applyConditionalBlocks(string $html, ?string $sexo): string
    {
        $sexo = strtoupper((string) $sexo);
        $isMale = $sexo === 'M';
        $isFemale = $sexo === 'F';

        $patterns = [
            '/\{\{#if_hombre\}\}(.*?)\{\{\/if_hombre\}\}/is' => $isMale ? '$1' : '',
            '/\{\{#if_mujer\}\}(.*?)\{\{\/if_mujer\}\}/is' => $isFemale ? '$1' : '',
        ];

        foreach ($patterns as $pattern => $replacement) {
            $html = preg_replace($pattern, $replacement, $html) ?? $html;
        }

        return $html;
    }

    private function normalizeFieldsSchema(?string $rawSchema): array
    {
        if (!$rawSchema) {
            return [];
        }

        $decoded = json_decode($rawSchema, true);
        if (!is_array($decoded)) {
            return [];
        }

        $allowedTypes = ['text', 'textarea', 'number', 'date', 'time', 'datetime-local', 'select', 'email', 'tel', 'checkbox'];

        return collect($decoded)
            ->map(function ($field, $index) use ($allowedTypes) {
                $label = trim((string) data_get($field, 'label', 'Campo ' . ($index + 1)));
                $key = trim((string) data_get($field, 'key'));
                $key = preg_replace('/[^a-z0-9_]/', '_', strtolower($key));
                $key = trim((string) $key, '_');

                if (!$label || !$key) {
                    return null;
                }

                $type = strtolower(trim((string) data_get($field, 'type', 'text')));
                if (!in_array($type, $allowedTypes, true)) {
                    $type = 'text';
                }

                $options = $type === 'select' && is_array(data_get($field, 'options'))
                    ? array_values(array_filter(array_map(fn ($option) => trim((string) $option), data_get($field, 'options')), fn ($option) => $option !== ''))
                    : [];

                return [
                    'key' => $key,
                    'label' => $label,
                    'type' => $type,
                    'required' => (bool) data_get($field, 'required', false),
                    'options' => $options,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }
}
