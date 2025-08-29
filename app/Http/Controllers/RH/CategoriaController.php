<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RH\categoria;

class CategoriaController extends Controller
{
    private categoria $categoriaModel;

    public function __construct()
    {
        // [ ] validar uso
        $this->categoriaModel = new categoria();
    }

    // corresponde a categoria->ListaCategorias()
    public function ListaCategorias()
    {
        // [ ] validar uso
        return response()->json($this->categoriaModel->ListaCategorias());
    }

    // corresponde a categoria->ObterCategoriaPorId()
    public function ObterCategoriaPorId($id)
    {
        // [ ] validar uso
        return response()->json($this->categoriaModel->ObterCategoriaPorId($id));
    }

    // corresponde a categoria->CriarCategoria()
    public function CriarCategoria(Request $request)
    {
        $payload = $request->all();
        // [ ] validar uso
        return response()->json($this->categoriaModel->CriarCategoria($payload));
    }

    // corresponde a categoria->AtualizarCategoria()
    public function AtualizarCategoria(Request $request, $id)
    {
        $payload = $request->all();
        $payload['id_categoria'] = $id;
        // [ ] validar uso
        return response()->json($this->categoriaModel->AtualizarCategoria($payload));
    }

    // corresponde a categoria->RemoverCategoria()
    public function RemoverCategoria(Request $request, $id)
    {
        $payload = $request->all();
        $payload['id_categoria'] = $id;
        // [ ] validar uso
        return response()->json($this->categoriaModel->RemoverCategoria($payload));
    }
}
