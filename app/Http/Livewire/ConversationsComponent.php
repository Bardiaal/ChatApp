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

class ConversationsComponent extends Component
{

    public $selectedChatType;

    public function render()
    {
        $chats = null;
        $myId = Auth::id();

        if($this->selectedChatType == 'private') {
            $chats = PrivateChat::select('*')
                ->where('user_1', Auth::id())
                ->orWhere('user_2', Auth::id())            
                ->get();
        } else {
            //$chats = GroupChat::
        }

        $pendingMessages = [];

        foreach($chats as $chat) {
            $lastMessage = Message::select('user_id', 'text')
                ->where('chat_id', $chat->id)
                ->latest('_id')
                ->first();
            $chat->whos_last_message = $lastMessage->user_id ?? '0';
            $chat->last_message = $lastMessage->text ?? 'Sin mensajes';
            $pendingMessages[$chat->id]['last_message'] = $lastMessage->text ?? '';
        }

        foreach($chats as $chat) {
            $pendingMessagesOfChat = Message::select('_id')
                ->where('chat_id', $chat->id)
                ->where("who_has_to_read_it.$myId", false)
                ->get();
            $pendingMessages[$chat->id]['pending_mark_as_read'] = [];
            foreach ($pendingMessagesOfChat as $messageId) {
                array_push($pendingMessages[$chat->id]['pending_mark_as_read'], $messageId->_id);
            }
        }

        return view('livewire.conversations-component', [
            'chats' => $chats,
            'pendingMessages' => $pendingMessages,
        ]);
    }
}
