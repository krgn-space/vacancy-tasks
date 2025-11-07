<?php

namespace App\Http\Controllers;

use App\Mail\TaskCreated;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function tasks(Request $request, $project_id)
    {
        $query = Task::where('project_id', $project_id);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('performer')) {
            $query->where('performer', $request->performer);
        }

        if ($request->has('completed_at')) {
            $query->whereDate('completed_at', $request->completed_at);
        }

        $tasks = $query->orderBy('created_at', 'desc')->get();

        return response()->json($tasks);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request, $project_id)
    {
        $validated = $request->validate([
            'performer' => 'required|integer|exists:users,id',
            'title' => 'required|string',
            'description' => 'required|string',
            'status' => 'string|in:planned,in_progress,done',
            'completed_at' => 'nullable|date',
            'file' => 'nullable|file|max:10240',
        ]);

        $task = new Task();
        $task->project_id = $project_id;
        $task->performer = $validated['performer'];
        $task->title = $validated['title'];
        $task->description = $validated['description'];
        $task->status = $validated['status'] ?? 'planned';
        $task->completed_at = $validated['completed_at'] ?? null;

        if ($request->hasFile('file')) {
            $task->addMediaFromRequest('file')->toMediaCollection('tasks');
        }

        $task->save();

        Mail::to(Auth::user()->email)->send(new TaskCreated($task));

        return response()->json([
            'message' => 'Задача создана.',
            'task' => $task,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function view(string $id)
    {
        $task = Task::with('media')->find($id);

        if (!$task) {
            return response()->json([
                'message' => 'Задача не найдена.'
            ]);
        }

        return response()->json([
            'task' => $task,
            'file' => $task->getMedia('tasks')[0]->getFullUrl()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json([
                'message' => 'Задача не найдена.'
            ], 400);
        }

        $validated = $request->validate([
            'project_id' => 'sometimes|required|integer',
            'performer' => 'sometimes|required|integer',
            'title' => 'sometimes|required|string',
            'description' => 'sometimes|required|string',
            'status' => 'sometimes|required|in:planned,in_progress,done',
            'completed_at' => 'nullable|date',
        ]);

        $task->update($validated);

        return response()->json([
            'message' => 'Задача обновлена.',
            'task' => $task
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json([
                'message' => 'Задача не найдена.'
            ], 400);
        }

        $task->delete();

        return response()->json([
            'message' => 'Задача удалена.',
        ]);
    }
}
