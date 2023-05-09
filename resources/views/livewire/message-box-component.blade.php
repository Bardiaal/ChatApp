<div id="chat-messages-box" class="chat-conversation" data-chatid="{{ $chatId }}" data-toid="{{ $toId }}" data-loadedmessages="{{ $loadedMessages }}">

    <div wire:loading.delay class="loading-state">
        <div class="lds-grid"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
        <p>Cargando mensajes...</p>
    </div>

    <div id="messages-display" class="messages-box">
    @if($chatId != '')
        @if($moreMessagesAvailable)
            <div id="load-more-btn" class="load-more" onclick="loadMoreMessages('{{ $chatId }}')">
                <p><i class="fas fa-arrow-up"></i> Cargar más mensajes</p>
            </div>
        @else
            <br>
        @endif
        @if(isset($messages)) 
        <?php 
            $now = Carbon\Carbon::now();
            $previousMessageDate = null;
        ?>
            @foreach($messages as $message)
                <?php
                    $messageDate = Carbon\Carbon::parse($message->date);
                    $year = $messageDate->format('Y');
                    $month = $messageDate->format('m');
                    $day = $messageDate->format('d');
                    $hourMinute = $messageDate->format('H:i');

                    $formattedDateForMessage = '';
                    if($now->day != $day || $now->month != $month || $now->year != $year) $formattedDateForMessage .= "$day/$month";
                    if($now->year != $year) $formattedDateForMessage .= "/$year";
                    $formattedDateForMessage .= " $hourMinute";
                    $formattedDateForMessage = trim($formattedDateForMessage);
                ?>
                @if($previousMessageDate != null && $previousMessageDate != $messageDate->format('d/m/Y'))
                <div class="date-divider">{{ $day . '/' . $month . '/' . $year }}</div>
                @endif
                <?php $previousMessageDate = $messageDate->format('d/m/Y') ?>
                @if($message->user_id == Auth::id())
                    <div id="message-{{ $message->id }}" class="local">
                        <div class="space"></div>
                        <div class="local-content @if(!$loop->last && $messages[$loop->index + 1]->user_id == $message->user_id) local-not-last @endif">
                            <p class="message-text">{{ $message->text }}</p>
                            <p class="date"><i>{{ $formattedDateForMessage }}</i><i class="message-flag fa-solid
                            @if((in_array(false, $message->who_has_to_read_it, true)))
                                fa-check
                            @else
                                fa-check-double
                            @endif "></i></p>
                        </div>
                    </div>
                    @if(!$loop->last && $messages[$loop->index + 1]->user_id != $message->user_id)
                    <br>
                    @endif
                @else
                    <div id="message-{{ $message->id }}" class="visitor">
                        <div class="visitor-content @if(!$loop->last && $messages[$loop->index + 1]->user_id == $message->user_id) visitor-not-last @endif">
                            <p class="message-text">{{ $message->text }}</p>
                            <p class="date"><i>{{ $formattedDateForMessage }}</i></p>
                        </div>
                        <div class="space"></div>
                    </div>
                    @if(!$loop->last && $messages[$loop->index + 1]->user_id != $message->user_id)
                    <br>
                    @endif
                @endif
            @endforeach
        @else
            <h2 id="initial-chat-message" class="initial-content-h1"><i class="fa-solid fa-ghost"></i> {{ $initial }}</h2>
        @endif
    @else
        <h1 id="initial-chat-message" class="initial-content-h1"><i class="fa-solid fa-comment-dots"></i> Pulsa sobre un chat para empezar</h1>
    @endif
    </div>
</div>
