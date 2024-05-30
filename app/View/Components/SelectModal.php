<?php

namespace App\View\Components;

use Illuminate\View\Component;

class SelectModal extends Component
{
    public $id;
    public $title;
    public $body;
    public $acceptRoute;
    public $nameSelect;
    public $ticketId;
    public $fieldName;
    public $eventSelected;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($id, $title, $body, $acceptRoute = null, $nameSelect = null, $ticketId = null, $fieldName = null, $eventSelected = null)
    {
        $this->id = $id;
        $this->title = $title;
        $this->body = $body;
        $this->acceptRoute = $acceptRoute;
        $this->nameSelect = $nameSelect;
        $this->ticketId = $ticketId;
        $this->fieldName = $fieldName;
        $this->eventSelected = $eventSelected;
    }
    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.select-modal');
    }
}
