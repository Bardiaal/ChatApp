<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PrivateChat;
use App\Models\GroupChat;
use App\Models\User;
use App\Models\Message;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use DB;

class ChatController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {   
        $selectedChatType = 'private';
        return view('home', [
            'selectedChatType' => $selectedChatType,
        ]);
    }

    /*----------------------- TEST AREA -------------------------*/

    public function testPrivateChat() {
        $users = User::all();
        $user_1 = $users[rand(0, sizeof($users) - 1)];
        $userid_1 = $user_1->id;
        $user_2 = $users[rand(0, sizeof($users) - 1)];
        $userid_2 = $user_2->id;
        if($userid_1 == $userid_2) {
            return;
        }
        $chats = PrivateChat::all();
        foreach($chats as $chat) {
            if(($userid_1 == $chat->user_1 && $userid_2 == $chat->user_2) || ($userid_1 == $chat->user_2 && $userid_2 == $chat->user_1)) {
                return;
            }
        }
        PrivateChat::create([
            'user_1' => $userid_1,
            'user_2' => $userid_2,
            'username_1' => $user_1->name,
            'username_2' => $user_2->name,
            'date' => Carbon::now()->toDateTimeString(),
        ]);
    }

    public function testGroupChat() {
        GroupChat::create([
            'chat_name' => 'Test Group ' . rand(1, 500),
            'date' => Carbon::now()->toDateTimeString(),
        ]);
    }

    public function testPrivateMessage() {
        $randNumber = rand(1, 500);
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $randNumber; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        $chats = PrivateChat::all();
        $chat = $chats[rand(0, sizeof($chats) - 1)];
        $users = User::all();
        $user = $users[rand(0, sizeof($users) - 1)];
        if($chat->user_1 != $user->id && $chat->user_2 != $user->id) return;
        Message::create([
            'chat_id' => $chat->id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'text' => $randomString,
            'date' => Carbon::now()->toDateTimeString(),
        ]);
        return $randomString;
    }

    public function testGroupMessage() {
        $randNumber = rand(1, 500);
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $randNumber; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        $chats = GroupChat::all();
        $users = User::all();
        $user = $users[rand(0, sizeof($users) - 1)];
        $groupsJoined = $user->group_ids;
        if($groupsJoined != null && !in_array($group->id, $groupsJoined)) {
            Message::create([
                'chat_id' => $chats[rand(0, sizeof($chats) - 1)]->id,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'text' => $randomString,
                'date' => Carbon::now()->toDateTimeString(),
            ]);
        } else {
            return;
        }
        return $randomString;
    }

    public function userJoinRandomGroup() {
        $users = User::all();
        $user = $users[rand(0, sizeof($users) - 1)];
        $user_id = $user->id;
        $groupsJoined = $user->group_ids;
        $groups = GroupChat::all();
        foreach($groups as $group) {
            if($groupsJoined != null && !in_array($group->id, $groupsJoined)) {
                User::find($users[rand(0, sizeof($users) - 1)]->id)->push('group_ids', $group->id);
                return;
            }
        }
    }

}
