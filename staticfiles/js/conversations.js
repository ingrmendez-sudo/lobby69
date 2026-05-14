// CONVERSATIONS - FUNCIONES ESPECÍFICAS

function openChat(convId, name, event) {
    document.getElementById('chatName').textContent = name;

    // Actualizar item activo
    document.querySelectorAll('.conversation-item').forEach(item => {
        item.classList.remove('active');
    });
    event.target.closest('.conversation-item').classList.add('active');

    // Mostrar chat area
    document.getElementById('chatArea').style.display = 'flex';
    document.getElementById('emptyState').style.display = 'none';

    // Scroll al último mensaje
    setTimeout(() => {
        const messagesContainer = document.getElementById('messagesContainer');
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }, 100);
}

function sendMessage() {
    const input = document.getElementById('messageInput');
    const message = input.value.trim();

    if (message) {
        // Crear elemento del mensaje
        const messagesContainer = document.getElementById('messagesContainer');
        const newMessage = document.createElement('div');
        newMessage.className = 'message sent';
        newMessage.innerHTML = `
            <div>
                <div class="message-bubble">${escapeHtml(message)}</div>
                <div class="message-time">${new Date().toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' })}</div>
            </div>
        `;
        messagesContainer.appendChild(newMessage);

        // Limpiar input
        input.value = '';
        input.style.height = 'auto';

        // Scroll al último mensaje
        messagesContainer.scrollTop = messagesContainer.scrollHeight;

        // Simular respuesta (después de 1 segundo)
        setTimeout(() => {
            const responseMessage = document.createElement('div');
            responseMessage.className = 'message received';
            responseMessage.innerHTML = `
                <div class="message-avatar">S</div>
                <div>
                    <div class="message-bubble">Gracias por tu mensaje 😊</div>
                    <div class="message-time">${new Date().toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' })}</div>
                </div>
            `;
            messagesContainer.appendChild(responseMessage);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }, 1000);
    }
}

// Función auxiliar para escapar HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Auto-resize textarea
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.getElementById('messageInput');
    if (textarea) {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 100) + 'px';
        });

        textarea.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });
    }

    // Buscar conversaciones
    const searchInput = document.getElementById('searchConv');
    if (searchInput) {
        searchInput.addEventListener('keyup', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            document.querySelectorAll('.conversation-item').forEach(item => {
                const name = item.querySelector('.conv-name').textContent.toLowerCase();
                item.style.display = name.includes(searchTerm) ? '' : 'none';
            });
        });
    }
});

// FUNCIÓN PARA VER HISTORIA
function viewStory(name) {
    alert('👁️ Viendo historia de ' + name);
    // Aquí puedes agregar lógica para mostrar la historia en modal
}

// Agregar hover effects a los anuncios
document.addEventListener('DOMContentLoaded', function() {
    const adItems = document.querySelectorAll('.ad-item');
    adItems.forEach(ad => {
        ad.addEventListener('mouseenter', function() {
            this.style.boxShadow = '0 8px 16px rgba(70, 130, 180, 0.2)';
            this.style.transform = 'translateY(-3px)';
        });
        ad.addEventListener('mouseleave', function() {
            this.style.boxShadow = 'none';
            this.style.transform = 'translateY(0)';
        });
    });

    // Scroll horizontal en historias
    const storiesCarousel = document.querySelector('.stories-carousel');
    if (storiesCarousel) {
        const storyItems = storiesCarousel.querySelectorAll('.story-item');
        storyItems.forEach(item => {
            item.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.08)';
            });
            item.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1)';
            });
        });
    }
});
