/* CONVERSATIONS PAGE - FUNCIONES ESPECÍFICAS */

document.addEventListener('DOMContentLoaded', function() {
  console.log('conversations.js: Inicializando...');

  initializeConversationsList();
  initializeSearchConversations();
  initializeMessageInput();
  initializeChatOptions();
  loadDefaultChat();
});

/**
 * LISTA DE CONVERSACIONES
 */
function initializeConversationsList() {
  document.querySelectorAll('.conversation-item').forEach(item => {
    item.addEventListener('click', function(e) {
      e.preventDefault();
      const conversationId = this.dataset.conversationId;
      const conversationName = this.querySelector('.conv-name').textContent;

      openChat(conversationId, conversationName);

      // Marcar como activo
      document.querySelectorAll('.conversation-item').forEach(i => {
        i.classList.remove('active');
      });
      this.classList.add('active');
    });
  });
}

function openChat(conversationId, conversationName) {
  console.log('Abriendo chat:', conversationId, conversationName);

  const chatNameEl = document.getElementById('chatName');
  if (chatNameEl) {
    chatNameEl.textContent = conversationName;
  }

  // Cargar mensajes via AJAX
  loadMessages(conversationId);
}

function loadDefaultChat() {
  const firstConversation = document.querySelector('.conversation-item');
  if (firstConversation) {
    firstConversation.click();
  }
}

/**
 * BÚSQUEDA DE CONVERSACIONES
 */
function initializeSearchConversations() {
  const searchInput = document.getElementById('searchConv');
  if (searchInput) {
    searchInput.addEventListener('input', debounce(handleSearchConversations, 300));
  }
}

function handleSearchConversations(event) {
  const searchTerm = event.target.value.toLowerCase();
  console.log('Buscando conversaciones:', searchTerm);

  document.querySelectorAll('.conversation-item').forEach(item => {
    const name = item.querySelector('.conv-name').textContent.toLowerCase();
    const message = item.querySelector('.conv-message').textContent.toLowerCase();

    if (name.includes(searchTerm) || message.includes(searchTerm)) {
      item.style.display = 'block';
    } else {
      item.style.display = 'none';
    }
  });
}

/**
 * MENSAJES
 */
function loadMessages(conversationId) {
  const csrfToken = getCookie('csrftoken');

  fetch(`/api/messages/${conversationId}/`, {
    method: 'GET',
    headers: {
      'X-CSRFToken': csrfToken,
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      renderMessages(data.messages);
      console.log('Mensajes cargados:', data.messages.length);
    }
  })
  .catch(error => console.error('Error al cargar mensajes:', error));
}

function renderMessages(messages) {
  const messagesContainer = document.getElementById('messagesContainer');
  if (!messagesContainer) return;

  messagesContainer.innerHTML = '';

  messages.forEach(msg => {
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${msg.is_sent ? 'sent' : 'received'}`;
    messageDiv.innerHTML = `
      ${!msg.is_sent ? `<div class="message-avatar">${msg.sender_initial}</div>` : ''}
      <div>
        <div class="message-bubble">${msg.text}</div>
        <div class="message-time">${msg.time}</div>
      </div>
      ${msg.is_sent ? `<div class="message-avatar">${msg.sender_initial}</div>` : ''}
    `;
    messagesContainer.appendChild(messageDiv);
  });

  // Scroll hacia abajo
  messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

/**
 * INPUT DE MENSAJE
 */
function initializeMessageInput() {
  const messageInput = document.querySelector('.message-input');
  const sendBtn = document.querySelector('.btn-send');

  if (messageInput && sendBtn) {
    sendBtn.addEventListener('click', sendMessage);
    messageInput.addEventListener('keypress', function(e) {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
      }
    });
  }
}

function sendMessage() {
  const messageInput = document.querySelector('.message-input');
  if (!messageInput || !messageInput.value.trim()) {
    console.log('Mensaje vacío');
    return;
  }

  const message = messageInput.value;
  const conversationItem = document.querySelector('.conversation-item.active');
  const conversationId = conversationItem?.dataset.conversationId;

  if (!conversationId) {
    console.error('No hay conversación activa');
    return;
  }

  const csrfToken = getCookie('csrftoken');

  fetch('/api/send-message/', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRFToken': csrfToken,
    },
    body: JSON.stringify({
      conversation_id: conversationId,
      message: message
    })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      console.log('Mensaje enviado');
      messageInput.value = '';
      loadMessages(conversationId);
    } else {
      console.error('Error al enviar mensaje:', data.error);
    }
  })
  .catch(error => console.error('Error:', error));
}

/**
 * OPCIONES DE CHAT
 */
function initializeChatOptions() {
  document.querySelectorAll('.chat-option-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      const action = this.title;
      console.log('Acción de chat:', action);

      // Aquí se implementarían las acciones (llamada, video, info)
    });
  });
}

/**
 * UTILIDADES
 */
function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

function getCookie(name) {
  let cookieValue = null;
  if (document.cookie && document.cookie !== '') {
    const cookies = document.cookie.split(';');
    for (let i = 0; i < cookies.length; i++) {
      const cookie = cookies[i].trim();
      if (cookie.substring(0, name.length + 1) === (name + '=')) {
        cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
        break;
      }
    }
  }
  return cookieValue;
}

console.log('conversations.js: Cargado exitosamente ✓');
