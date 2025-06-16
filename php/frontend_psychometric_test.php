<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
$userName = $_SESSION['user_name'];
$userEmail = $_SESSION['user_email'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>20:20 FC - FINEDICA</title>
    <link rel="stylesheet" href="../css/main.css">
    <style>
        .question-block {
            background: #fff;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .category-title {
            color: #07f9a8;
            font-size: 1.2em;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 2px solid #07f9a8;
        }
        .question-item {
            margin-bottom: 15px;
            padding: 10px;
            background: #f9f9f9;
            border-radius: 5px;
        }
        .submit-btn {
            background: #07f9a8;
            color: #222;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            transition: all 0.3s;
        }
        .submit-btn:hover {
            background: #05d98f;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .result-block {
            background: #e0ffe0;
            padding: 25px;
            margin-top: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }
        .radio-group {
            margin-top: 8px;
        }
        .radio-group label {
            display: block;
            margin: 5px 0;
            cursor: pointer;
        }
        .highlight-missing {
            background: #ffeaea;
            border: 2px solid #e53935;
        }
    </style>
</head>
<body>
<header>
    <nav class="navbar">
        <div class="logo">
            <h1>20:20 FC - FINEDICA</h1>
            <p>Expert Financial Coaching</p>
        </div>
        <ul>
            <li><a href="home.php">Home</a></li>
            <li><a href="questionnaire.php">Questionnaire</a></li>
            <li><a href="#contact">Contact</a></li>
            <li><a href="avatar.php">Avatar</a></li>
            <li><a href="chatbot.php">Chatbot</a></li>
            <li><a href="logout.php" style="font-size: 14px; color:rgb(7, 249, 168)">Logout <?php echo htmlspecialchars($userName);?></a></li>
        </ul>
    </nav>  
</header>
<main>
    <h2>Psychometric Money Behaviour Test</h2>
    <form id="psychometricForm">
        <input type="hidden" name="email" value="<?php echo htmlspecialchars($userEmail); ?>">
        <div id="questions-container"></div>
        <button type="submit" class="submit-btn">Submit Assessment</button>
    </form>
    <div id="result" class="result-block" style="display:none;"></div>
</main>

<script>
const questions = {
    "Money Avoidance": [
        "I do not deserve a lot of money when others have less than me.",
        "Rich people are greedy.",
        "People get rich by taking advantage of others.",
        "I do not deserve money.",
        "Good people should not care about money."
    ],
    "Money Worship": [
        "Things would get better if I had more money.",
        "More money will make you happier.",
        "It is hard to be poor and happy.",
        "You can never have enough money.",
        "Money is power."
    ],
    "Money Status": [
        "Most poor people do not deserve to have money.",
        "You can have love or money, but not both.",
        "I will not buy something unless it is new (e.g., car, house).",
        "Poor people are lazy.",
        "Money is what gives life meaning."
    ],
    "Money Vigilance": [
        "You should not tell others how much money you have or make.",
        "It is wrong to ask others how much money they have or make.",
        "Money should be saved not spent.",
        "It is important to save for a rainy day.",
        "People should work for their money and not be given financial handouts."
    ]
};

const container = document.getElementById('questions-container');

// Dynamically generate the questions and radio buttons
for (const [category, qs] of Object.entries(questions)) {
    const catDiv = document.createElement('div');
    catDiv.className = 'question-block';
    catDiv.innerHTML = `<div class="category-title">${category}</div>`;
    
    qs.forEach((q, i) => {
        catDiv.innerHTML += `
        <div class="question-item">
            <label>${q}</label>
            <div class="radio-group">
                <label><input type="radio" name="${category}[${i}]" value="1" required> 1 - Strongly Disagree</label>
                <label><input type="radio" name="${category}[${i}]" value="2"> 2 - Disagree</label>
                <label><input type="radio" name="${category}[${i}]" value="3"> 3 - Neutral</label>
                <label><input type="radio" name="${category}[${i}]" value="4"> 4 - Agree</label>
                <label><input type="radio" name="${category}[${i}]" value="5"> 5 - Strongly Agree</label>
            </div>
        </div>`;
    });
    container.appendChild(catDiv);
}

// Handle form submission
document.getElementById('psychometricForm').addEventListener('submit', function(e) {
    e.preventDefault();
    // Highlight missed questions
    let hasMissing = false;
    document.querySelectorAll('.question-item').forEach(item => {
        const checked = item.querySelector('input[type="radio"]:checked');
        if (!checked) {
            item.classList.add('highlight-missing');
            hasMissing = true;
        } else {
            item.classList.remove('highlight-missing');
        }
    });
    // Validation
    const totalQuestions = Object.values(questions).reduce((sum, qs) => sum + qs.length, 0);
    const answered = document.querySelectorAll('input[type="radio"]:checked').length;
    if (answered !== totalQuestions) {
        alert(`Please answer all ${totalQuestions} questions (${totalQuestions - answered} remaining).`);
        const firstMissing = document.querySelector('.question-item.highlight-missing');
        if (firstMissing) firstMissing.scrollIntoView({behavior: 'smooth'});
        return;
    }
    // Prepare data for PHP
    const formData = new FormData();
    formData.append('email', '<?php echo htmlspecialchars($userEmail); ?>');
    Object.keys(questions).forEach(category => {
        document.querySelectorAll(`input[name^="${category}"]:checked`).forEach((radio, idx) => {
            formData.append(`${category}[${idx}]`, radio.value);
        });
    });
    // AJAX submission
    fetch('psychometric_test_api.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'ok') {
            // Show results and redirect
            const resultDiv = document.getElementById('result');
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = `
                <h3>Test Results</h3>
                <p><strong>Dominant Belief:</strong> ${data.top_category}</p>
                <p>${data.description}</p>
            `;
            setTimeout(() => {
                window.location.href = '../future_self/futureself.php';
            }, 3500);
        } else {
            alert(`Error: ${data.message || 'Unknown error'}`);
        }
    })
    .catch(err => {
        alert('An error occurred while saving your responses. Please try again.');
        console.error('Error:', err);
    });
});
</script>

</body>
</html>
<?php