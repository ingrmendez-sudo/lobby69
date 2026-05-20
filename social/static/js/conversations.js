console.log("conversations.js cargando...");

document.addEventListener("DOMContentLoaded", function() {
    console.log("DOMContentLoaded");
    
    // Send button
    document.getElementById("sendBtn")?.addEventListener("click", enviarMensaje);
    
    // Message input - Enter key
    document.getElementById("messageInput")?.addEventListener("keypress", function(e) {
        if (e.key === "Enter") {
            e.preventDefault();
            enviarMensaje();
        }
    });
    
    // Search
    document.getElementById("searchConv")?.addEventListener("keyup", function() {
        const q = this.value.toLowerCase();
        document.querySelectorAll(".conversation-item").forEach(item => {
            const name = (item.querySelector(".conv-name")?.textContent || "").toLowerCase();
            const msg = (item.querySelector(".conv-message")?.textContent || "").toLowerCase();
            item.style.display = (name + msg).includes(q) ? "flex" : "none";
        });
    });
    
    // Conversation items click
    document.querySelectorAll(".conversation-item").forEach(item => {
        item.addEventListener("click", function() {
            document.querySelectorAll(".conversation-item").forEach(i => {
                i.style.borderLeft = "3px solid transparent";
                i.style.backgroundColor = "#f9f9f9";
            });
            this.style.borderLeft = "3px solid #4682B4";
            this.style.backgroundColor = "rgba(70, 130, 180, 0.1)";
            
            const name = this.getAttribute("data-conv-name");
            document.getElementById("chatName").textContent = name;
            document.getElementById("infoName").textContent = name;
        });
    });
    
    // Mute button
    document.getElementById("muteBtn")?.addEventListener("click", function() {
        mostrarAlerta("🔇 Conversación silenciada");
    });
    
    // Block button
    document.getElementById("blockBtn")?.addEventListener("click", function() {
        if (confirm("¿Bloquear usuario?")) {
            mostrarAlerta("✅ Usuario bloqueado");
        }
    });
    
    // Delete button
    document.getElementById("deleteBtn")?.addEventListener("click", function() {
        const active = document.querySelector(".conversation-item[style*='4682B4']");
        if (!active) {
            alert("Selecciona una conversación");
            return;
        }
        if (confirm("¿Eliminar?")) {
            active.remove();
            const first = document.querySelector(".conversation-item");
            if (first) first.click();
        }
    });
    
    // Open first conversation
    const first = document.querySelector(".conversation-item");
    if (first) first.click();
    
    console.log("✅ Inicialización completada");
});

function enviarMensaje() {
    const input = document.getElementById("messageInput");
    const text = (input.value || "").trim();
    
    if (!text) {
        console.log("Mensaje vacío");
        return;
    }
    
    console.log("Enviando:", text);
    
    const container = document.getElementById("messagesContainer");
    const time = new Date().toLocaleTimeString("es-ES", {hour:"2-digit", minute:"2-digit"});
    
    // Mensaje enviado
    const div = document.createElement("div");
    div.style.cssText = "display:flex;gap:12px;justify-content:flex-end;margin:10px 0";
    div.innerHTML = `
        <div>
            <div style="max-width:60%;padding:12px 16px;border-radius:15px;background:#4682b4;color:white">
                ${text.replace(/</g,"&lt;").replace(/>/g,"&gt;")}
            </div>
            <div style="font-size:11px;color:#999;text-align:right;margin-top:5px">${time}</div>
        </div>
    `;
    container.appendChild(div);
    container.scrollTop = container.scrollHeight;
    
    input.value = "";
    input.focus();
    
    // Respuesta simulada
    setTimeout(function() {
        const replies = ["¡Hola!","Perfecto","Suena bien 😊","¿Mañana?","Me encantaría"];
        const reply = replies[Math.floor(Math.random() * replies.length)];
        const time2 = new Date().toLocaleTimeString("es-ES", {hour:"2-digit", minute:"2-digit"});
        
        const div2 = document.createElement("div");
        div2.style.cssText = "display:flex;gap:12px;margin:10px 0";
        div2.innerHTML = `
            <div style="width:35px;height:35px;border-radius:50%;background:#4682b4;color:white;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-weight:700">S</div>
            <div>
                <div style="max-width:60%;padding:12px 16px;border-radius:15px;background:#e3f2fd;color:#333">${reply}</div>
                <div style="font-size:11px;color:#999;margin-top:5px">${time2}</div>
            </div>
        `;
        container.appendChild(div2);
        container.scrollTop = container.scrollHeight;
    }, 2000);
}

function mostrarAlerta(texto) {
    const alert = document.createElement("div");
    alert.style.cssText = "position:fixed;top:20px;right:20px;padding:12px 16px;background:#d1ecf1;color:#0c5460;border-radius:6px;z-index:9999;font-size:13px";
    alert.textContent = texto;
    document.body.appendChild(alert);
    setTimeout(() => alert.remove(), 2000);
}

console.log("✅ conversations.js listo");
