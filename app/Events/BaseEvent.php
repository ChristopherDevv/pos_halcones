<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BaseEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $isPublic = false;
    protected $data  = [];
    protected $user = null;
    protected $channel = '';
    protected $listen = null;

    /**
     * @param bool $isPublic Indicar si el evento es público o privado
     * @param array $data La
     * @param null $user
     * @param string $channel
     * @param string $listen
     */
    public function __construct(bool $isPublic, array $data, $user, string $channel, string $listen)
    {
        $this->isPublic = $isPublic;
        $this->data = $data;
        $this->user = $user;
        $this->channel = $channel;
        $this->listen = $listen;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
       if(!$this->isPublic){
           if(is_null($this->user)) {
               throw new \Exception('No hay un usuario encontrado');
           }
           return new PrivateChannel($this->channel.'.'.$this->user->id);
       }else {
           return new Channel($this->channel);
       }
    }

    public function broadcastAs()
    {
        if(is_null($this->channel)) {
            throw new \Exception('No se ha agregado un listen para el eveento');
        }else {
            return $this->listen;
        }
    }
    public function broadcastWith(): array
    {
        return $this->data;
    }

    /**
     * @return bool
     */
    public function isPublic(): bool
    {
        return $this->isPublic;
    }

    /**
     * @param bool $isPublic
     */
    public function setIsPublic(bool $isPublic): void
    {
        $this->isPublic = $isPublic;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * @return null
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param null $user
     */
    public function setUser($user): void
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getChannel(): string
    {
        return $this->channel;
    }

    /**
     * @param string $channel
     */
    public function setChannel(string $channel): void
    {
        $this->channel = $channel;
    }

    /**
     * @return string|null
     */
    public function getListen(): ?string
    {
        return $this->listen;
    }

    /**
     * @param string|null $listen
     */
    public function setListen(?string $listen): void
    {
        $this->listen = $listen;
    }

}
