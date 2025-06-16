// src/js/main.js



class Website {

    
    constructor() {
        this.auth = new AuthManager();
        this.questionnaire = new Questionnaire();
        this.avatarGenerator = new AvatarGenerator();
        this.chatbot = new Chatbot();
        
        this.initializeEventListeners();
    }

    initializeEventListeners() {
        document.getElementById('signupForm')?.addEventListener('submit', this.handleSignup.bind(this));
        document.getElementById('faceUpload')?.addEventListener('change', this.handleFaceUpload.bind(this));
        document.getElementById('userInput')?.addEventListener('keypress', this.handleChatInput.bind(this));
    }

    handleSignup(event) {
        event.preventDefault();
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        this.auth.signup(email, password);
    }

    handleFaceUpload(event) {
        const file = event.target.files[0];
        this.avatarGenerator.generateAvatar(file);
    }

    handleChatInput(event) {
        if (event.key === 'Enter') {
            const message = document.getElementById('userInput').value;
            this.chatbot.sendMessage(message);
        }
    }
}

// Initialize the website
const website = new Website();

document.addEventListener('DOMContentLoaded', () => {
    const faceUploadInput = document.getElementById('faceUpload');
    const uploadBtn = document.getElementById('uploadBtn');
    const generateAvatarBtn = document.getElementById('generateAvatarBtn');
    const avatarContainer = document.getElementById('avatarContainer');

    // Upload face image
    uploadBtn.addEventListener('click', () => {
        const file = faceUploadInput.files[0];
        if (!file) {
            alert('Please select a face image to upload.');
            return;
        }

        const formData = new FormData();
        formData.append('faceImage', file);

        fetch('../php/avatar.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Face image uploaded successfully!');
                console.log('Face Image URL:', data.faceImageUrl);
            } else {
                alert('Failed to upload face image.');
            }
        })
        .catch(error => {
            console.error('Error uploading face image:', error);
        });
    });

    // Generate avatar
    generateAvatarBtn.addEventListener('click', () => {
        const hairColor = document.getElementById('hairColor').value;
        const eyeColor = document.getElementById('eyeColor').value;
        const skinTone = document.getElementById('skinTone').value;
        const clothingColor = document.getElementById('clothingColor').value;

        // Simulate avatar generation (replace with actual logic if needed)
        const avatarData = `data:image/png;base64,${btoa('Generated Avatar Data')}`;

        // Display generated avatar
        const img = document.createElement('img');
        img.src = avatarData;
        avatarContainer.innerHTML = '';
        avatarContainer.appendChild(img);

        // Send avatar to backend
        fetch('../php/avatar.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ avatarData: avatarData.split(',')[1] })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Avatar uploaded successfully!');
                console.log('Avatar Image URL:', data.avatarImageUrl);
            } else {
                alert('Failed to upload avatar.');
            }
        })
        .catch(error => {
            console.error('Error uploading avatar:', error);
        });
    });
});

// src/js/auth.js (continued)
function showLogin() {
    document.getElementById('login-form').classList.add('active');
    document.getElementById('signup-form').classList.remove('active');
    document.querySelectorAll('.tab-button').forEach(button => 
        button.classList.remove('active')
    );
    document.querySelector('#login-tab').classList.add('active');
}

function showSignup() {
    document.getElementById('signup-form').classList.add('active');
    document.getElementById('login-form').classList.remove('active');
    document.querySelectorAll('.tab-button').forEach(button => 
        button.classList.remove('active')
    );
    document.querySelector('#signup-tab').classList.add('active');
}

document.getElementById('loginForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const email = document.getElementById('login-email').value;
    const password = document.getElementById('login-password').value;
    
    try {
        const user = await authManager.login(email, password);
        console.log('Logged in successfully:', user);
        // Redirect to welcome page
        window.location.href = 'home.php';
    } catch (error) {
        console.error('Login failed:', error);
        alert('Login failed. Please check your credentials.');
    }
});

document.getElementById('signupForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const email = document.getElementById('signup-email').value;
    const password = document.getElementById('signup-password').value;
    const confirmPassword = document.getElementById('signup-confirm-password').value;
    
    if (password !== confirmPassword) {
        alert('Passwords do not match!');
        return;
    }
    
    try {
        const user = await authManager.signup(email, password);
        console.log('Signed up successfully:', user);
        // Redirect to welcome page
        window.location.href = 'index.html';
    } catch (error) {
        console.error('Signup failed:', error);
        alert('Signup failed. Please try again.');
    }
});

