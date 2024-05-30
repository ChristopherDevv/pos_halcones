<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Exports\TicketsExport;
use Illuminate\Support\Collection;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TicketsTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_example()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_tickets_export_collection_method()
    {
        $tickets = new TicketsExport(126);
        $collection = $tickets->collection();

        $this->assertNotNull($collection);
        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertNotEmpty($collection);
    }
}
