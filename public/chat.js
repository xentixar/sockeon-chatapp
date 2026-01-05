import {api} from "./api.js";

api('/api/user', 'GET', {}, true)
    .then(response => {
        if (!response) {
            location.href = 'login.html?msg=unauthorized'
        }
    })

const socket = new WebSocket('ws://localhost:6001')
let typingSetTimeout = null

const handleConnectedEvent = ({message, clientId}) => {
    renderSystemMessage(`${clientId} has joined the chat.`)
}

const handleMessageReceivedEvent = ({message, username, timestamps, is_me}) => {
    if (is_me) {
        renderSentMessage({username, message, timestamps});
    } else {
        renderReceivedMessage({username, message, timestamps});
    }
}

const handleMessageTypingStartReceived = ({clientId}) => {
    const typingIndicator = document.querySelector('#typing-indicator');
    typingIndicator.innerHTML = `<i>${clientId} is typing...</i>`
}

const handleMessageTypingStopReceived = ({clientId}) => {
    const typingIndicator = document.querySelector('#typing-indicator');
    typingIndicator.innerHTML = `<i>...</i>`
}

const renderSentMessage = ({username, message, timestamps}) => {
    const chatSection = document.querySelector('#chat');
    chatSection.innerHTML += `<div class="flex justify-end w-full">
    <div class="bg-blue-600 px-2 py-1 my-4 text-white rounded flex flex-col gap-2 text-right max-w-[600px]">
        <span class="font-medium">${username}</span>
        <span class="text-gray-50 text-sm ">${message}</span>
        <span class="text-gray-100 text-sm">${timestamps}</span>
    </div>
</div>`;
}

const renderReceivedMessage = ({username, message, timestamps}) => {
    const chatSection = document.querySelector('#chat');
    chatSection.innerHTML += `<div class="flex justify-start w-full">
    <div class="bg-gray-500 px-2 py-1 my-4 text-white rounded flex flex-col gap-2 max-w-[600px]">
        <span class="font-medium">${username}</span>
        <span class="text-gray-50 text-sm ">${message}</span>
        <span class="text-gray-100 text-sm">${timestamps}</span>
    </div>
</div>`
}

const renderSystemMessage = (message) => {
    const chatSection = document.querySelector('#chat');
    chatSection.innerHTML += `<div class="w-full text-center text-gray-500 my-4">
        <strong>System:</strong>
        <i>${message}</i>
    </div>`
}

const handleEvent = (data) => {
    switch (data.event) {
        case "connected":
            handleConnectedEvent(data.data);
            break;
        case "message.received":
            handleMessageReceivedEvent(data.data);
            break;
        case "message.typing.start.received":
            handleMessageTypingStartReceived(data.data);
            break;
        case "message.typing.stop.received":
            handleMessageTypingStopReceived(data.data);
            break;
        default:
            console.warn(`Event ${data.event} is not handled!!`);
    }
}

const emit = (event, data) => {
    const json = {
        event: event,
        data: data
    }
    socket.send(JSON.stringify(json))
}

socket.addEventListener('open', function (event) {
    console.info("Socket is connected!!")
});

socket.addEventListener('message', function (event) {
    handleEvent(JSON.parse(event.data));
});

const sendMessageForm = document.querySelector('#send-message-form');
sendMessageForm.addEventListener('submit', function (event) {
    event.preventDefault();

    emit('message.typing.stop', {})
    emit('send.message', {
        message: message.value
    })
});

const message = document.querySelector('#message');
message.addEventListener('keydown', function (event) {
    emit('message.typing.start', {})
    if (typingSetTimeout) {
        clearTimeout(typingSetTimeout);
    }
    typingSetTimeout = setTimeout(function () {
        emit('message.typing.stop', {})
    }, 2000)
})