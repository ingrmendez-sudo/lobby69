document.addEventListener("DOMContentLoaded", function() {
    console.log(">> conversations.js init");
    
    // Search
    const search = document.getElementById("searchConv");
    if (search) {
        search.addEventListener("keyup", function() {
            const q = this.value.toLowerCase();
            document.querySelectorAll(".conversation-item").forEach(item => {
                const name = (item.querySelector(".conv-name")?.textContent || "").toLowerCase();
                const msg = (item.querySelector(".conv-message")?.textContent || "").toLowerCase();
                item.style.display = (name + msg).includes(q) ? "flex" : "none";
            });
        });
    }
    
    // Conversation click
    document.addEventListener("click", function(e) {
        const convItem = e.target.closest(".conversation-item");
        if (convItem) {
            document.querySelectorAll(".conversation-item").forEach(item => {
                item.style.borderLeft = "3px solid transparent";
                item.style.backgroundColor = "#f9f9f9";
            });
            convItem.style.borderLeft = "3px solid #4682B4";
            convItem.style.backgroundColor = "rgba(70, 130, 180, 0.1)";
            
            const name = convItem.getAttribute("data-conv-name");
            document.getElementById("chatName").textContent = name;
            document.getElementById("infoName").textContent = name;
        }
    });
    
    // Send message
    const sendBtn = document.getElementById("sendBtn");
    const msgInput = document.getElementById("messageInput");
    
    if (sendBtn) {
        sendBtn.addEventListener("click", sendMessage);
    }
    if (msgInput) {
        msgInput.addEventListener("keypress", function(e) {
            if (e.key === "Enter") {
                e.preventDefault();
                sendMessage();
            }
        });
    }
    
    // Mute button
    const muteBtn = document.getElementById("muteBtn");
    if (muteBtn) {
        muteBtn.addEventListener("click", function() {
            const msg = document.createElement("div");
            msg.style.cssText = "position:fixed;top:20px;right:20px;padding:12px 16px;background:#d1ecf1;color:#0c5460;border-radius:6px;z-index:9999;font-size:13px";
            msg.textContent = "🔇 Conversación silenciada";
            document.body.appendChild(msg);
            setTimeout(() => msg.remove(), 2000);
        });
    }
    
    // Block button
    const blockBtn = document.getElementById("blockBtn");
    if (blockBtn) {
        blockBtn.addEventListener("click", function() {
            if (confirm("¿Bloquear usuario?")) {
                const msg = document.createElement("div");
                msg.style.cssText = "position:fixed;top:20px;right:20px;padding:12px 16px;background:#d4edda;color:#155724;border-radius:6px;z-index:9999;font-size:13px";
                msg.textContent = "✅ Usuario bloqueado";
                document.body.appendChild(msg);
                setTimeout(() => msg.remove(), 2000);
            }
        });
    }
    
    // Delete button
    const deleteBtn = document.getElementById("deleteBtn");
    if (deleteBtn) {
        deleteBtn.addEventListener("click", function() {
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
    }
    
    // Click first conversation
    setTimeout(() => {
        const first = document.querySelector(".conversation-item");
        if (first) first.click();
    }, 100);
});

function sendMessage() {
    const input = document.getElementById("messageInput");
    const text = (input?.value || "").trim();
    if (!text) return;
    
    const container = document.getElementById("messagesContainer");
    const time = new Date().toLocaleTimeString("es-ES", {hour:"2-digit", minute:"2-digit"});
    const safe = text.replace(/</g,"&lt;").replace(/>/g,"&gt;");
    
    const div = document.createElement("div");
    div.style.cssText = "display:flex;gap:12px;justify-content:flex-end;margin:10px 0";
    div.innerHTML = `
        <div>
            <div style="max-width:60%;padding:12px 16px;border-radius:15px;background:#4682b4;color:white">
                ${safe}
            </div>
            <div style="font-size:11px;color:#999;text-align:right;margin-top:5px">
                ${time}
            </div>
        </div>
    `;
    
    container.appendChild(div);
    container.scrollTop = container.scrollHeight;
    input.value = "";
    
    setTimeout(() => {
        const replies = ["¡Hola!","Perfecto","Suena bien 😊","¿Mañana?","Me encantaría"];
        const reply = replies[Math.floor(Math.random()*replies.length)];
        const time2 = new Date().toLocaleTimeString("es-ES", {hour:"2-digit", minute:"2-digit"});
        
        const div2 = document.createElement("div");
        div2.style.cssText = "display:flex;gap:12px;margin:10px 0";
        div2.innerHTML = `
            <div style="width:35px;height:35px;border-radius:50%;background:#4682b4;color:white;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-weight:700">
                S
            </div>
            <div>
                <div style="max-width:60%;padding:12px 16px;border-radius:15px;background:#e3f2fd;color:#333">
                    ${reply}
                </div>
                <div style="font-size:11px;color:#999;margin-top:5px">
                    ${time2}
                </div>
            </div>
        `;
        
        container.appendChild(div2);
        container.scrollTop = container.scrollHeight;
    }, 2000);
}

console.log("✅ conversations.js ready");
