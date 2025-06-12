document.addEventListener('DOMContentLoaded', function () {
    const generateBtn = document.getElementById('generate-avatar-btn');
    const avatarPreview = document.getElementById('avatar-preview');
    const userEmail = window.userEmail; // from <script> where you echoed userEmail

    generateBtn.addEventListener('click', function () {
        console.log("Generate button clicked");

        fetch('../php/age_avayar_gen.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email: userEmail })
        })
        .then(response => response.json())
        .then(data => {
            console.log(data);
            if (data.status === 'ok') {
                avatarPreview.innerHTML = `<img src="${data.avatar_path}" alt="Generated Avatar" style="max-width:300px;">`;
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });
});
