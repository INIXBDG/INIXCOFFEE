<?php

namespace App\Kanbans;

use JinoAntony\Kanban\Kanban;
use App\Models\Task;

class TaskKanban extends Kanban
{
    public function scripts()
    {
        $data = $this->data();
        $updateUrl = route('tasks.update-state');

        return <<<HTML
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var kanban = new jKanban({
                    element: '.kanban-board',
                    gutter: '15px',
                    widthBoard: '300px',
                    boards: [
                        { id: '_todo', title: 'To Do', class: 'info', item: {$this->toJson($data['todo'])} },
                        { id: '_inprogress', title: 'In Progress', class: 'warning', item: {$this->toJson($data['inprogress'])} },
                        { id: '_done', title: 'Done', class: 'success', item: {$this->toJson($data['done'])} }
                    ],

                    dropEl: function(el, target, source, sibling) {
                        const taskId = el.dataset.eid;
                        const newState = target.parentElement.getAttribute('data-id').replace('_', '');

                        console.log('✅ DEBUG Update Task:', { taskId, newState });

                        fetch("{$updateUrl}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ id: taskId, state: newState })
                        })
                        .then(res => res.json())
                        .then(data => {
                            console.log('Response:', data);
                            if (!data.success) {
                                alert('❌ ' + data.message);
                                console.error('Error:', data.errors);
                            }
                        })
                        .catch(err => console.error('Fetch error:', err));
                    }
                });
            });
        </script>
        HTML;
    }

    private function toJson($data)
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }


    public function data(): array
    {
        $tasks = Task::all();

        return [
            'todo' => $tasks->where('state', 'todo')->map(fn ($task) => [
                'id' => $task->id,
                'title' => "
                    <div data-id='{$task->id}'>
                        <strong>{$task->title}</strong><br>
                        <small class='text-muted'>{$task->description}</small>
                    </div>
                ",
            ])->values()->toArray(),

            'inprogress' => $tasks->where('state', 'inprogress')->map(fn ($task) => [
                'id' => $task->id,
                'title' => "
                    <div data-id='{$task->id}'>
                        <strong>{$task->title}</strong><br>
                        <small class='text-muted'>{$task->description}</small>
                    </div>
                ",
            ])->values()->toArray(),

            'done' => $tasks->where('state', 'done')->map(fn ($task) => [
                'id' => $task->id,
                'title' => "
                    <div data-id='{$task->id}'>
                        <strong>{$task->title}</strong><br>
                        <small class='text-muted'>{$task->description}</small>
                    </div>
                ",
            ])->values()->toArray(),
        ];
    }

    public function getBoards(): array
    {
        return [
            ['title' => 'To Do', 'key' => 'todo'],
            ['title' => 'In Progress', 'key' => 'inprogress'],
            ['title' => 'Done', 'key' => 'done'],
        ];
    }
}
