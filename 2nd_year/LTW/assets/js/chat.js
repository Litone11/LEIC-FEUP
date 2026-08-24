document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('chat-form');
  const input = document.getElementById('message-input');
  const container = document.getElementById('chat-messages');

  async function fetchMessages() {
    const res = await fetch(`../../includes/fetch_messages.php?sender_id=${SENDER_ID}&receiver_id=${RECEIVER_ID}`);
    const messages = await res.json();
    container.innerHTML = '';
    messages.forEach(msg => {
      const bubble = document.createElement('div');
      bubble.className = 'chat-bubble ' + (msg.sender_id == SENDER_ID ? 'sent' : 'received');
      bubble.innerHTML = `<div class="bubble-content">${msg.content}</div><div class="bubble-time">${msg.sent_at}</div>`;
      container.appendChild(bubble);
    });
    container.scrollTop = container.scrollHeight;
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const content = input.value.trim();
    if (!content) return;

    await fetch('../../includes/send_message.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ sender_id: SENDER_ID, receiver_id: RECEIVER_ID, message: content })
    });

    input.value = '';
    fetchMessages();
  });

  setInterval(fetchMessages, 3000);
  fetchMessages();
});