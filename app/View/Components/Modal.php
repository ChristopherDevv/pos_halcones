<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Modal extends Component
{
    public $id;
    public $title;
    public $body;
    public $acceptRoute;
    public $user; 
    public $modelId;
    public $modelValue;
    public $eventSelected;
    
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($id, $title, $body, $acceptRoute = null, $user = null, $modelId = null, $modelValue = null, $eventSelected = null) 
    {
        $this->id = $id;
        $this->title = $title;
        $this->body = $body;
        $this->acceptRoute = $acceptRoute;
        $this->user = $user; 
        $this->modelId = $modelId;
        $this->modelValue = $modelValue;
        $this->eventSelected = $eventSelected;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.modal');
    }
}