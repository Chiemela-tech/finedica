import os
import json
import random
import string
import torch
import nltk
from flask import Flask, request, jsonify
from flask_cors import CORS
from nltk.corpus import stopwords
from sklearn.feature_extraction.text import TfidfVectorizer
from model import ChatbotModel
from data_loader import load_intents

nltk.download('stopwords')
stop_words = set(stopwords.words('english'))

app = Flask(__name__)
CORS(app)

base_dir = os.path.dirname(os.path.abspath(__file__))

def safe_load_json(path):
    try:
        with open(path, "r") as f:
            return json.load(f)
    except Exception as e:
        print(f"Failed to load {path}: {e}")
        return {}

vocabulary, intents, intent_responses = load_intents(os.path.join(base_dir, "intents.json"))
dimensions = safe_load_json(os.path.join(base_dir, "dimensions.json"))
tfidf_vocab = safe_load_json(os.path.join(base_dir, "tfidf_vocab.json"))

# Load model
if dimensions:
    model = ChatbotModel(dimensions.get('input_size', 0), dimensions.get('output_size', 0))
    try:
        model.load_state_dict(torch.load(os.path.join(base_dir, "chatbot_model.pth")))
        model.eval()
    except Exception as e:
        print(f"Model load error: {e}")
else:
    model = None

# Load and fit TF-IDF vectorizer
vectorizer = TfidfVectorizer(vocabulary=tfidf_vocab if tfidf_vocab else None)
if intent_responses:
    vectorizer.fit([" ".join(patterns) for patterns in intent_responses.values()])

def preprocess_text(sentence):
    sentence = sentence.lower()
    tokens = nltk.word_tokenize(sentence)
    tokens = [word for word in tokens if word not in string.punctuation and word not in stop_words]
    return " ".join(tokens)

@app.route('/chat', methods=['POST'])
def chat():
    try:
        data = request.get_json()
        user_message = data.get('message', '').strip().lower()
        # Only intent-based response
        processed = preprocess_text(user_message)
        X = vectorizer.transform([processed]).toarray()
        with torch.no_grad():
            output = model(torch.tensor(X, dtype=torch.float32))
            predicted = torch.argmax(output, dim=1).item()
            tag = intents[predicted]
            responses = intent_responses.get(tag, ["I'm not sure how to help with that."])
            response = random.choice(responses)
        return jsonify({'response': response})
    except Exception as e:
        print(f"Error: {e}")
        return jsonify({'response': f"An error occurred: {str(e)}"}), 500

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5003)
