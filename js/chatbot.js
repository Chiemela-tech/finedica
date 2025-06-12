// Chatbot Functionality
function sendMessage() {
    const userInput = document.getElementById('userInput').value.trim();
    if (!userInput) return;

    const chatHistory = document.getElementById('chatHistory');
    const userMessage = `<div class="message user">${userInput}</div>`;
    chatHistory.innerHTML += userMessage;

    // Send the message to the Flask API
    fetch('http://localhost:5000/chat', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: userInput })
    })
    .then(res => res.json())
    .then(data => {
        const botMessage = `<div class="message bot">${data.response}</div>`;
        chatHistory.innerHTML += botMessage;
        chatHistory.scrollTop = chatHistory.scrollHeight;
    })
    .catch(err => {
        console.error('Error:', err);
        const errorMessage = `<div class="message bot">Sorry, something went wrong. Please try again later.</div>`;
        chatHistory.innerHTML += errorMessage;
    });

    document.getElementById('userInput').value = '';
}