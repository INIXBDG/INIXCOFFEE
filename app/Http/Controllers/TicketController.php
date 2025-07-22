<?php

namespace App\Http\Controllers;

use App\Models\Tickets;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class TicketController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'no_user'   => 'required|string',
            'deskripsi' => 'required|string',
        ]);

        $ticket = Tickets::create([
            'no_user'   => $request->no_user,
            'deskripsi' => $request->deskripsi,
            'status'    => 'Menunggu',
        ]);

        return response()->json(['ticket_id' => $ticket->id], 201);
    }


    public function index()
    {
        $tickets = Tickets::all();
        return view('ticket.index', compact('tickets'));
    }

    public function accept(Request $request, Tickets $ticket)
    {
        $ticket->update([
            'status'  => 'Di Proses',
            'id_ts'   => Auth::id(),
        ]);

        $id = Auth::id();
        $username = User::where('id', $id)->first();
        $ts = $username->username;

        Http::post('http://localhost:3000/notify', [
            'phone'     => $ticket->no_user,
            'status'    => 'Di Proses',
            'ticket_id' => $ticket->id,
            'ts' => $ts,
        ]);

        return redirect()->route('tickets.index')->with('success', 'Tiket diterima.');
    }

    public function finish(Request $request, Tickets $ticket)
    {
        $ticket->update([
            'status' => 'Selesai',
            'alasan' => $request->alasan,
        ]);

        Http::post('http://localhost:3000/notify', [
            'phone'           => $ticket->no_user,
            'status'          => 'Selesai',
            'ticket_id'       => $ticket->id,
            'resolution_notes' => $request->alasan,
        ]);

        return redirect()->route('tickets.index')->with('success', 'Tiket selesai.');
    }

    public function block(Request $request, Tickets $ticket)
    {
        $ticket->update([
            'status' => 'Terkendala',
            'alasan' => $request->alasan,
        ]);

        Http::post('http://localhost:3000/notify', [
            'phone'           => $ticket->no_user,
            'status'          => 'Terkendala',
            'ticket_id'       => $ticket->id,
            'resolution_notes' => $request->alasan,
        ]);

        return redirect()->route('tickets.index')->with('success', 'Tiket ditandai sebagai terkendala.');
    }

    public function show(Tickets $ticket)
    {
        return view('ticket.detail', compact('ticket'));
    }
}
