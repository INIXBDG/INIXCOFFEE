<?php

namespace App\Http\Controllers;

use App\Models\ScheduleLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Carbon\Carbon;

class ScheduleLogController extends Controller
{
    public function index(): View
    {
        return view('schedule.index');
    }

    public function data(Request $request): JsonResponse
    {
        $query = ScheduleLog::query();

        $totalRecords = $query->count();

        if ($request->has('search') && !empty($request->input('search.value'))) {
            $searchValue = $request->input('search.value');
            $query->where('command_name', 'like', '%' . $searchValue . '%')
                  ->orWhere('status', 'like', '%' . $searchValue . '%')
                  ->orWhere('error_message', 'like', '%' . $searchValue . '%');
        }

        $filteredRecords = $query->count();

        if ($request->has('order')) {
            $orderColumnIndex = $request->input('order.0.column');
            $orderDirection = $request->input('order.0.dir');
            
            $columns = ['id', 'command_name', 'status', 'error_message', 'execution_date'];
            
            if (isset($columns[$orderColumnIndex])) {
                $query->orderBy($columns[$orderColumnIndex], $orderDirection);
            }
        } else {
            $query->orderBy('id', 'desc');
        }

        if ($request->has('start') && $request->has('length')) {
            $start = $request->input('start');
            $length = $request->input('length');
            if ($length != -1) {
                $query->skip($start)->take($length);
            }
        }

        $logs = $query->get();

        return response()->json([
            'draw'            => intval($request->input('draw')),
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data'            => $logs->map(function ($log) {
                return [
                    'id'             => $log->id,
                    'command_name'   => $log->command_name,
                    'status'         => $log->status,
                    'error_message'  => $log->error_message,
                    'execution_date' => Carbon::parse($log->execution_date)->format('Y-m-d'),
                ];
            })
        ]);
    }

    public function clearAll(): JsonResponse
    {
        ScheduleLog::truncate();

        return response()->json([
            'status' => 'success',
            'message' => 'Seluruh log berhasil dihapus.'
        ]);
    }
}