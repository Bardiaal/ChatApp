@extends('layouts.main')
@section('content')
<livewire:conversations-component :selectedChatType="$selectedChatType"/> 
<div class="chat">
    <div class="chats-top">
        <img id="user-img" src="" alt="" style="display: none;">
        <div id="user-name">
            <h4 id="user-name-chat"></h4>
            <h6 id="user-writing"></h6>
        </div>
        <div class="manage-component" data-toggle="tooltip" data-placement="bottom" title="Todo parece estar bien :)">
            <livewire:manage-message-component />
        </div>
        <div class="chat-options">
            <i class="fas fa-ellipsis-h"></i>
        </div>
    </div>
    <livewire:message-box-component :selectedChatType="$selectedChatType"/> 
    <div class="chat-input">
        <button type="button" id="append-button" class="append chat-input-group" style="display: none"><i class="fas fa-paperclip"></i></button>
        <form id="send-form" autocomplete="off">
            <div id="chat-input-box" class="chat-input chat-input-group" style="display: none">
                <input id="input-text" type="text" placeholder="Escribe un mensaje..."></input>
                <div class="send-message">
                    <button type="submit" form="send-form" id="send"><i class="fas fa-paper-plane"></i></button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
@section('scripts')
<script>

    var myId = '{{Auth::id()}}';
    var chatId = '';
    var sendForm = document.getElementById('send-form');
    var inputSend = document.getElementById('input-text');
    var manageMessageComponent = document.getElementById('manage-message');

    $(function () {
        $('#manage-message').tooltip({
            trigger: 'hover'
        }); 
    })
    /*
    query:{
        userid: "{{Auth::user()->socket_id}}",
    */
    var socket = io('192.168.0.23:3000', {
        transports : ['websocket'], 
        query:"userid={{Auth::id()}}"
    });

    socket.on('connect', () => {
        console.log(socket.id); 
        manageMessageComponent.innerHTML = '<i class="fa-solid fa-check"></i>';
    });

    socket.on("connect_error", (err) => {
        manageMessageComponent.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i>';
    });

    socket.on('send', (message) => {
        console.log(message);
        alert(message);
    });

    socket.on('private-message', (message) => {
        //console.log(message);
        var msg = JSON.parse(message);
        document.getElementById('date-' + msg.chat_id).innerText = msg.date;
        if(!(msg.chat_id in pendingMessages)) {
            pendingMessages[msg.chat_id] = {};
            pendingMessages[msg.chat_id]['pending_messages'] = 0;
        }
        if(!(msg.chat_id in pendingMarkAsRead)) {
            pendingMarkAsRead[msg.chat_id] = [];
        }
        pendingMessages[msg.chat_id]['last_message'] = msg.message;
        if(chatId == msg.chat_id) {
            document.getElementById('last-message-' + msg.chat_id).innerText = msg.message;
            Livewire.emit('mark-as-read', msg.chat_id, msg.from, msg.message_id);
            if(document.getElementById('initial-chat-message')) document.getElementById('initial-chat-message').remove();
            var formattedDate = new Date(msg.date);
            createPrivateMessageBubble(msg.message_id, msg.message, (formattedDate.getHours() < 10 ? '0':'') + formattedDate.getHours() + ':' + (formattedDate.getMinutes() < 10 ? '0':'') + formattedDate.getMinutes(), 'visitor');
        } else {
            pendingMessages[msg.chat_id]['pending_messages'] += 1 
            var pendingCounter = pendingMessages[msg.chat_id]['pending_messages'];
            pendingMarkAsRead[msg.chat_id].push(msg.message_id);
            document.getElementById('last-message-' + msg.chat_id).innerHTML = '<span id="badge-' + msg.chat_id + '" class="badge badge-pending">' + pendingCounter + '</span> ' + msg.message;
            $('#last-message-' + msg.chat_id).addClass('message-not-read');
        }
    });

    // User connected or writing
    // <i class="fa-solid fa-circle-dot text-success"></i> En línea
    // <i class="fa-solid fa-circle-dot text-danger"></i> Última conexión: 
    // <i class="fa-solid fa-circle-dot text-primary writing-icon"></i> Escribiendo...
    socket.on('set-user-status', (data) => {
        console.log('User status received: ' + data);
        var connData = JSON.parse(data);
        if(connData._id == myId) return;
        if(document.getElementById('chat-messages-box').dataset.toid == connData._id) {
            console.log('Showing: ' + true);
            if(connData.online) document.getElementById('user-writing').innerHTML = '<i class="fa-solid fa-circle-dot text-success"></i> En línea';
            else document.getElementById('user-writing').innerHTML = '<i class="fa-solid fa-circle-dot text-danger"></i> Última conexión: ' + connData.last_connection_date;
        }
        document.querySelectorAll('[data-chattingwith~="' + connData._id + '"]')[0].style.display = 'block';
        document.getElementById('writing-' + connData._id).style.display = 'none';
    });

    socket.on('pending-messages-viewed', (chatId) => {
        console.log('Pending messages viewed: ' + chatId);
        if(chatId == this.chatId) {
            $('.message-flag').removeClass('fa-check').addClass('fa-check-double');
        }
    });

    function loadChatMessages(id) {
        if(chatId == id) return;
        inputSend.disabled = true;
        if (chatId != '') $('#conversation-' + chatId).removeClass('selected-conversation');
        else $('.chat-input-group').fadeIn();
        chatId = id;
        document.getElementById('user-writing').innerHTML = '';
        $('#conversation-' + id).addClass('selected-conversation');
        document.getElementById('user-name-chat').innerText = document.getElementById('name-' + id).dataset.name;
        document.getElementById('user-img').src = document.getElementById('img-' + id).src;
        document.getElementById('user-img').style.display = 'block';
        document.getElementById('messages-display').style.display = 'none';
        if(document.getElementById('badge-' + id)) {
            document.getElementById('badge-' + id).remove();
            if(id in pendingMessages) pendingMessages[id]['pending_messages'] = 0;
        }
        Livewire.emit('chat-id', id, document.getElementById('name-' + id).dataset.toid);
    }

    function loadMoreMessages(id) {
        inputSend.disabled = true;
        document.getElementById('load-more-btn').style.display = 'none';
        var msgId = document.querySelector('#messages-display :nth-child(2)').id;
        Livewire.emit('more-messages', id, document.getElementById('name-' + id).dataset.toid, Number(document.getElementById('chat-messages-box').dataset.loadedmessages), msgId);
    }

    function createPrivateMessageBubble(id, text, date, owner) {
        var bubble = document.createElement('div');
        bubble.id = 'message-' + id; 
        bubble.className = owner;
        var bubbleInner = '';
        var flag = '';
        if(owner == 'local') {
            bubbleInner += '<div class="space"></div>';
            flag = '<i class="message-flag fa-solid fa-check"></i>';
        }
        bubbleInner += '<div class="' + owner + '-content"><p class="message-text">' + text + '</p><p class="date"><i>' + date + '</i>' + flag + '</p></div>';
        if(owner == 'visitor') bubbleInner += '<div class="space"></div>';
        bubble.innerHTML = bubbleInner;
        var previousBubble = document.getElementById('messages-display').lastElementChild;
        if(previousBubble.className == owner) previousBubble.querySelector('.' + owner + '-content').className = owner + '-content ' + owner + '-not-last';
        else document.getElementById('messages-display').appendChild(document.createElement('br'));
        document.getElementById('messages-display').appendChild(bubble);
        bubble.scrollIntoView({behavior: "smooth"});
    }

    sendForm.addEventListener("submit", function(event) {
        event.preventDefault();
        if(inputSend.value != "") {
            manageMessageComponent.innerHTML = '<div class="lds-ring"><div></div><div></div><div></div><div></div></div>';
            var whoHasToReadId = {};
            document.getElementById('chat-messages-box').dataset.toid.split(';').forEach(element => {
                whoHasToReadId[element] = false;
            });
            Livewire.emit('save-message', inputSend.value.trim(), JSON.stringify(whoHasToReadId));
            inputSend.value = "";
        }
    }, true);

    Livewire.on('message-saved', function(id, userid, text, date) {
        document.getElementById('last-message-' + chatId).innerText = 'Tú: ' + text;
        document.getElementById('date-' + chatId).innerText = date;
        if(document.getElementById('initial-chat-message')) document.getElementById('initial-chat-message').remove();
        var formattedDate = new Date(date);
        createPrivateMessageBubble(id, text, (formattedDate.getHours() < 10 ? '0':'') + formattedDate.getHours() + ':' + (formattedDate.getMinutes() < 10 ? '0':'') + formattedDate.getMinutes(), 'local');
        sendPrivateMessage(chatId, id, userid, document.getElementById('chat-messages-box').dataset.toid, text, date);
    });

    function sendPrivateMessage(chatId, messageId, from, to, text, date) {
        socket.send(JSON.stringify({
            "chat_id" : chatId,
            "message_id" : messageId,
            "from" : from,
            "to" : to,
            "message" : text,
            "date" : date
        }));
    }

    Livewire.on('messages-loaded', function(chatId, toId) {
        document.getElementById('chat-messages-box').scrollTop = document.getElementById('chat-messages-box').scrollHeight;
        socket.emit('get-user-status', toId);
        if(chatId in pendingMarkAsRead && pendingMarkAsRead[chatId].length != 0) Livewire.emit('mark-as-read-multiple', chatId, toId, JSON.stringify(pendingMarkAsRead[chatId]))
        inputSend.disabled = false;
    });

    Livewire.on('more-messages-loaded', function(msgId) {
        document.getElementById(msgId).scrollIntoView();
        inputSend.disabled = false;
    });

    Livewire.on('messages-marked', function(chatId, toId) {
        pendingMessages[chatId]['pending_messages'] = 0;
        pendingMarkAsRead[chatId] = [];
        socket.emit('notify-pending-messages-viewed', JSON.stringify({
            "chat_id" : chatId,
            "to" : toId,
        }));
    });

    var postTypingStatus = true;
    var throttleTime = 1000; 

    $('#input-text').on('keyup', function(event) {
        var key = event.keyCode || event.charCode;
        if(key === 13) return;
        if(postTypingStatus) {
            socket.emit('typing-to-private', document.getElementById('chat-messages-box').dataset.toid);
            postTypingStatus = false;
            setTimeout(function() {
                postTypingStatus = true;
            }, throttleTime);
        }
    });

    var clearInterval = 2000;
    var clearTimerIds = {};

    socket.on('received-typing-private', (userid) => {
        /* Escribiendo... en usuario en sidebar
        var lastMessage = document.getElementById('last-message-' + userid).innerText;
        document.getElementById('last-message-' + userid).innerText = 'Escribiendo...';
        $('#last-message-' + userid).addClass('message-not-read');
        */
        var toId = document.getElementById('chat-messages-box').dataset.toid;
        if(userid == toId) {
            document.getElementById('user-writing').innerHTML = '<i class="fa-solid fa-circle-dot text-primary writing-icon"></i> Escribiendo...';
        }
        document.querySelectorAll('[data-chattingwith~="' + userid + '"]')[0].style.display = 'none';
        document.getElementById('writing-' + userid).style.display = 'block';

        clearTimeout(clearTimerIds[userid]);
        clearTimerIds[userid] = setTimeout(function () {
            //if(document.getElementById('last-message-' + userid).dataset.messageread == "true") $('#last-message-' + userid).removeClass('message-not-read');
            //if(userid == toId) {
            socket.emit('get-user-status', userid);
            //}
        }, clearInterval);
    });

    function openNewConversationWindow() {
        if(document.getElementById('start-new-conversation').dataset.open == 'false') {
            document.getElementById('start-new-conversation').dataset.open = 'animating';
            $('#start-new-conversation').slideToggle(500, function() {
                document.getElementById('conversation-new-btn-txt').innerHTML = '<i class="fa-solid fa-times"></i> Cerrar';
                document.getElementById('start-new-conversation').dataset.open = 'true';
            });
        } else if(document.getElementById('start-new-conversation').dataset.open == 'true') {
            document.getElementById('start-new-conversation').dataset.open = 'animating';
            $('#start-new-conversation').slideToggle(500, function() {
                document.getElementById('conversation-new-btn-txt').innerHTML = '<i class="fa-solid fa-plus"></i> Nueva conversación';
                document.getElementById('start-new-conversation').dataset.open = 'false'; 
            });
        }
    }

    document.getElementById('start-conversation-input').addEventListener("input", function() {
        console.log(document.getElementById('start-conversation-input').value)
        if(document.getElementById('start-conversation-input').value != '') {
            document.getElementById('conversation-new-btn-txt').innerHTML = '<i class="fa-solid fa-comments"></i> Empezar';
            document.getElementById('start-new-conversation').dataset.open = 'writing';
        } else {
            document.getElementById('conversation-new-btn-txt').innerHTML = '<i class="fa-solid fa-times"></i> Cerrar';
            document.getElementById('start-new-conversation').dataset.open = 'true';
        }
    })

</script>
@endsection
