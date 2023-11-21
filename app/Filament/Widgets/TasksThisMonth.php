<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class TasksThisMonth extends Widget
{
    protected static string $view = 'filament.widgets.tasks-this-month';

    protected static ?int $sort = 1;
    public array $data = [];

    public function mount(): void
    {
        $this->data = [
            'money'          =>  '1278€ (1145€)',
            'hours'          => '32 ' . __('hours'),
            'monthFormatted' => date('M y'),
        ];
    }
}
