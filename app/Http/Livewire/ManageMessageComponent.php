<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\GroupChat;
use App\Models\PrivateChat;
use App\Models\Message;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use Auth;
use DB;

use Illuminate\Support\Facades\Log;

class ManageMessageComponent extends Component
{
    public $chatId = "";

    protected $listeners = [
        'chat-id' => 'setChatId',
        'save-message' => 'saveMessage',
        'mark-as-read' => 'markMessageAsRead',
        'mark-as-read-multiple' => 'markMessageAsReadMultiple',
    ];

    public function setChatId($id) {
        $this->chatId = $id;
    }

    public function saveMessage($text, $toId) {
        if($text == '') return;
        $date = Carbon::now()->toDateTimeString();
        $user = Auth::user();
        $message = Message::create([
            'chat_id' => $this->chatId,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'text' => $text,
            'date' => $date,
            'who_has_to_read_it' => json_decode($toId),
        ]);
        $this->emit('message-saved', $message->id, $user->id, $text, $date);
    }

    public function markMessageAsRead($chatId, $toId, $messageId) {
        $userId = Auth::id();
        Message::where('_id', $messageId)->update([
            "who_has_to_read_it.$userId" => true
        ]);
        $this->emit('messages-marked', $chatId, $toId);
    }

    public function markMessageAsReadMultiple($chatId, $toId, $messages) {
        $ids = json_decode($messages);
        $userId = Auth::id();
        Message::whereIn('_id', $ids)->update([
            "who_has_to_read_it.$userId" => true
        ]);
        $this->emit('messages-marked', $chatId, $toId);
    }

    public function render()
    {
        return view('livewire.manage-message-component', [
            'chatId' => $this->chatId,
        ]);
    }
}
