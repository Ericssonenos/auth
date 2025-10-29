<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RH\categoriaModal;

class CategoriaController extends Controller
{
    private categoriaModal $categoriaModel;

    public function __construct()
    {
        // [ ] validar uso
        $this->categoriaModel = new categoriaModal();
    }

       /**
     * Obter dados das categorias para select
     */
    public function ObterCategorias(Request $request)
    {
        $respostaCategorias = $this->categoriaModel->ObterCategorias($request->all());
        if ($respostaCategorias['status']) {
            $status = 200;
        }
        return response()->json($respostaCategorias, $status ?? 400);
    }


    // corresponde a categoriaModal->CriarCategoria()
    public function CriarCategoria(Request $request)
    {
        $payload = $request->all();
        // [ ] validar uso
        return response()->json($this->categoriaModel->CriarCategoria($payload));
    }

    // corresponde a categoriaModal->AtualizarCategoria()
    public function AtualizarCategoria(Request $request, $id)
    {
        $payload = $request->all();
        $payload['id_Categoria'] = $id;
        // [ ] validar uso
        return response()->json($this->categoriaModel->AtualizarCategoria($payload));
    }

    // corresponde a categoriaModal->RemoverCategoria()
    public function RemoverCategoria(Request $request, $id)
    {
        $payload = $request->all();
        $payload['id_Categoria'] = $id;
        // [ ] validar uso
        return response()->json($this->categoriaModel->RemoverCategoria($payload));
    }
}
