<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\GroupChat;
use App\Models\PrivateChat;
use App\Models\Message;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use Auth;

class MessageBoxComponent extends Component
{
    public $chatId = '';
    public $toId = '';
    public $loadedMessages = 20;

    protected $listeners = [
        'chat-id' => 'loadMessages',
        'more-messages' => 'loadMoreMessages',
    ];

    public function loadMessages($id, $toId) {
        $this->chatId = $id;
        $this->toId = $toId;
        $this->loadedMessages = 20;
        $this->emit('messages-loaded', $id, $this->toId);
    }

    public function loadMoreMessages($id, $toId, $howMany, $msgId) {
        $this->chatId = $id;
        $this->toId = $toId;
        $this->loadedMessages = $howMany + 20;
        $this->emit('more-messages-loaded', $msgId);
    }

    public function render()
    {
        if($this->chatId != '') {
            $messages = Message::select('_id', 'chat_id', 'user_id', 'text', 'date', 'who_has_to_read_it')->where('chat_id', $this->chatId)->latest('_id');
            $totalMessages = $messages->count();
            $messages = $messages->take($this->loadedMessages)->get()->reverse()->values();
            $moreMessagesAvailable = ($totalMessages > $this->loadedMessages) ? true : false;
            //dd($totalMessages);
            if(sizeof($messages) > 0) {
                return view('livewire.message-box-component', [
                    'chatId' => $this->chatId,
                    'toId' => $this->toId,
                    'loadedMessages' => $this->loadedMessages,
                    'moreMessagesAvailable' => $moreMessagesAvailable,
                    'messages' => $messages
                ]);
            } else {
                return view('livewire.message-box-component', [
                    'chatId' => $this->chatId,
                    'toId' => $this->toId,
                    'loadedMessages' => $this->loadedMessages,
                    'moreMessagesAvailable' => $moreMessagesAvailable,
                    'initial' => 'Todavía no hay nada por aquí...',
                ]);
            }
        }
        return view('livewire.message-box-component', [
            'chatId' => $this->chatId,
            'toId' => $this->toId,
            'loadedMessages' => $this->loadedMessages,
            'initial' => 'Pulsa sobre una conversación para chatear o empieza una nueva conversación',
        ]);
    }
}
