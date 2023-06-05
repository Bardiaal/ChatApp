<div class="conversations">
    <div class="conversations-top">
        <div class="conversations-title">
            <h1 id="app-title">Hola {{Auth::user()->name}}</h1>
        </div>
        <div class="conversations-options">
            <i class="fas fa-ellipsis-v"></i>
        </div>
    </div>
    <div class="conversation-search">
        <i class="fas fa-search"></i><input type="text" id="search" placeholder="">
    </div>
    <div class="conversation-types">
        <div class="conversation-type-tab @if($selectedChatType == 'private') selected @endif" data-type="private">
            <i class="fa-solid fa-user"></i><h5 class="conversation-type-label"> Privados</h5>
        </div>
        <div class="conversation-type-tab @if($selectedChatType == 'group') selected @endif" data-type="group">
            <i class="fa-solid fa-users"></i><h5 class="conversation-type-label"> Grupos</h5>
        </div>
    </div>
    <div class="conversations-chats">
        @if($selectedChatType == 'private')
            @foreach($chats as $chat)
            <?php 
                $nameUser = ($chat->user_1 == Auth::id()) ? $chat->username_2 : $chat->username_1;
                $toId = ($chat->user_1 == Auth::id()) ? $chat->user_2 : $chat->user_1;
            ?>
            <div id="conversation-{{$chat->id}}" onclick="loadChatMessages('{{$chat->id}}', '{{$toId}}')" class="conversation-chat">
                <img id="img-{{$chat->id}}" class="conversation-img" src="https://eu.ui-avatars.com/api/?name={{ str_replace(' ', '+', $nameUser) }}" alt="">
                <div class="conversation-name">
                    <div class="conversation-name-usr">
                        <div id="name-{{$chat->id}}" data-name="{{ $nameUser }}" data-toid="{{ $toId }}" class="conversation-name-user"><b>{{ $nameUser }}</b></div>
                        <div class="space"></div>
                        <div id="date-{{$chat->id}}" class="conversation-name-date"><i>{{ $chat->date }}</i></div>
                    </div>
                    <p id="last-message-{{$chat->id}}" data-chattingwith="{{ $toId }}" class="conversation-name-message">
                        @if($chat->whos_last_message != '0' && $chat->whos_last_message == Auth::id()) Tú: @endif
                        @if(sizeof($pendingMessages[$chat->id]['pending_mark_as_read']) != 0) <span id="badge-{{ $chat->id }}" class="badge badge-pending">{{ sizeof($pendingMessages[$chat->id]['pending_mark_as_read']) }}</span> @endif {{ $chat->last_message }}
                    </p>
                    <p id="writing-{{$toId}}" data-iswriting="{{ $toId }}" class="writing-sidebar" style="display: none;">Escribiendo...</p>
                </div>
            </div>
            @endforeach
        @endif
    </div>
    <div class="space"></div>
    <div id="start-new-conversation" style="display: none;" data-open="false">
        <h5 id="start-new-conversation-text">
            Escribe el correo del usuario con el que quieres empezar una conversación:
        </h5>
        <br>
        <input id="start-conversation-input" type="search" placeholder="Correo electrónico"></input>
    </div>
    <div id="conversations-new" onclick="openNewConversationWindow()">
        <h4 id="conversation-new-btn-txt" class="conversations-new-title"><i class="fa-solid fa-plus"></i> <span class="conversations-new-text">Nueva conversación</span></h4>
    </div>
    <script>
        var pendingMessages = {
            @foreach($pendingMessages as $key => $pendingMessage)
                '{{ $key }}': {
                    'last_message': '{{$pendingMessage["last_message"]}}',
                    'pending_messages': {{ sizeof($pendingMessage['pending_mark_as_read']) }}
                } @if(!$loop->last) , @endif
            @endforeach
        };
        var pendingMarkAsRead = {
            @foreach($pendingMessages as $key => $pendingMessage)
                '{{ $key }}': [
                    @foreach($pendingMessage['pending_mark_as_read'] as $messageId)
                        '{{ $messageId }}' @if(!$loop->last) , @endif
                    @endforeach
                ] @if(!$loop->last) , @endif
            @endforeach
        };
    </script>
</div>
