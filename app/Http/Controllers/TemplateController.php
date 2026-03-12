<?php

namespace App\Http\Controllers;

use App\Models\Template;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;

class TemplateController extends Controller
{
    // Mostrar lista de plantillas
    public function index()
    {
        $templates = Template::with('service')->get();
        return view('templates.index', compact('templates'));
    }

    // Formulario para crear
    public function create()
    {
        $services = Service::all();
        return view('templates.create', compact('services'));
    }

    // Guardar nueva plantilla
    public function store(Request $request)
    {
        $data = $request->validate([
            'service_id' => 'required|exists:services,id',
            'nombre_plantilla' => 'required|string|max:255',
            'html_content' => 'required'
        ]);

        Template::create($data);
        return redirect()->route('templates.index')->with('success', 'Plantilla creada con éxito.');
    }

    // Formulario para editar
    public function edit($id)
    {
        $template = Template::findOrFail($id);
        $services = Service::all();
        return view('templates.edit', compact('template', 'services'));
    }

    // Actualizar plantilla
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'service_id' => 'required|exists:services,id',
            'nombre_plantilla' => 'required|string|max:255',
            'html_content' => 'required'
        ]);

        Template::findOrFail($id)->update($data);
        return redirect()->route('templates.index')->with('success', 'Plantilla actualizada.');
    }

    // Eliminar con validación de integridad
    public function destroy($id)
    {
        try {
            Template::findOrFail($id)->delete();
            return response()->json(['success' => true]);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false, 
                'message' => 'No se puede eliminar la plantilla porque tiene registros asociados.'
            ], 409);
        }
    }

    public function preview(Template $template)
    {
        // Datos de ejemplo para la previsualización
        $datos = [
            '{{nombre_paciente}}' => 'JUAN PÉREZ GARCÍA',
            '{{dni_paciente}}'    => '78945612',
            '{{fecha_actual}}'    => date('d/m/Y'),
            '{{codigo_orden}}'    => 'ORD-2026-0001'
        ];

        $htmlPrevisualizado = str_replace(array_keys($datos), array_values($datos), $template->html_content);

        return view('templates.preview', compact('htmlPrevisualizado'));
    }
}
