<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\TodoAdministrasi;
use Illuminate\Http\Request;

class TodoAdministrasiController extends Controller
{
    public function index()
    {
        $todos = TodoAdministrasi::orderByDesc('created_at')->get();

        return view('crm.todoAdministrasi.index', compact('todos'));
    }

    public function store(Request $request)
    {
        $todo = $request->validate([
            'case' => 'required|string',
            'catatan' => 'nullable|string',
        ]);

        TodoAdministrasi::create($todo);

        return redirect()->route('todo-administrasi.index')->with('success', 'Todo berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $todo = $request->validate([
            'case' => 'required|string',
            'solusi' => 'nullable|string',
            'status' => 'required|in:progres,selesai,gagal',
            'catatan' => 'nullable|string',
        ]);

        $todoItem = TodoAdministrasi::findOrFail($id);
        $todoItem->update($todo);

        return redirect()->route('todo-administrasi.index')->with('success', 'Todo berhasil diupdate.');
    }
    public function destroy($id)
    {
        $todoItem = TodoAdministrasi::findOrFail($id);
        $todoItem->delete();

        return redirect()->route('todo-administrasi.index')->with('success', 'Todo berhasil dihapus.');
    }}
