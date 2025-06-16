import os
import json
import random
import string
import torch
import nltk
nltk.download('punkt')  # Fixed: use 'punkt' instead of 'punkt_tab'
from flask import Flask, request, jsonify
from flask_cors import CORS
from nltk.corpus import stopwords
from sklearn.feature_extraction.text import TfidfVectorizer
from model import ChatbotModel
from data_loader import load_intents

# LangChain imports
from pathlib import Path
from langchain_community.document_loaders import DirectoryLoader, TextLoader
from langchain_community.vectorstores import Chroma
from langchain_huggingface import HuggingFaceEmbeddings
from langchain_ollama import OllamaLLM
from langchain.chains import RetrievalQA

nltk.download('stopwords')
stop_words = set(stopwords.words('english'))

app = Flask(__name__)
CORS(app)
# Load intents and model dimensions
base_dir = os.path.dirname(os.path.abspath(__file__))
parent_dir = os.path.dirname(base_dir)

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
# === LangChain RAG Setup ===
try:
    # Load documents from ../data/
    data_dir = os.path.join(parent_dir, "data")
    loader = DirectoryLoader(str(data_dir), glob="**/*.txt", loader_cls=TextLoader, show_progress=True)
    docs = loader.load()
    print(f"LangChain: Loaded {len(docs)} documents from {data_dir}")

    # Use a local embedding model
    embeddings = HuggingFaceEmbeddings(model_name="BAAI/bge-small-en-v1.5")
    vectordb = Chroma.from_documents(docs, embeddings, persist_directory=os.path.join(base_dir, "langchain_chroma_db"))
    retriever = vectordb.as_retriever(search_kwargs={"k": 3})

    # Use Ollama as the LLM
    llm = OllamaLLM(model="phi3", base_url="http://localhost:11434")
    qa_chain = RetrievalQA.from_chain_type(
        llm=llm,
        retriever=retriever,
        return_source_documents=True,
        chain_type="stuff"
    )
    rag_enabled = True
except Exception as e:
    import traceback
    print(f"LangChain RAG setup failed: {e}\n{traceback.format_exc()}")
    rag_enabled = False
@app.route('/chat', methods=['POST'])
def chat():
    try:
        data = request.get_json()
        user_message = data.get('message', '').strip().lower()
        user_gender = data.get('gender', '').strip().capitalize() if data.get('gender') else None
        user_email = data.get('email', '').strip().lower() if data.get('email') else None

        # Personalization/profile code (optional, can be expanded)
        user_profile = {}
        psychometric_results = {}
        futureself_results = {}
        expenditure_data = {}
        if user_email:
            try:
                import mysql.connector
                conn = mysql.connector.connect(
                    host='localhost',
                    user='root',
                    password='finedica',
                    database='user_reg_db',
                    port=3306,  # Make sure this is the correct port
                    auth_plugin='mysql_native_password'
                )
                cursor = conn.cursor(dictionary=True)
                cursor.execute('SELECT first_name, last_name, gender FROM users WHERE email=%s LIMIT 1', (user_email,))
                user_profile = cursor.fetchone() or {}
                cursor.execute('SELECT * FROM expenditure WHERE email=%s ORDER BY id DESC LIMIT 1', (user_email,))
                expenditure_data = cursor.fetchone() or {}
                cursor.execute('SELECT * FROM future_self_responses WHERE email=%s ORDER BY id DESC LIMIT 1', (user_email,))
                futureself_results = cursor.fetchone() or {}
                # Fetch psychometric test results from MySQL, not SQLite
                cursor.execute('SELECT * FROM psychometric_test_responses WHERE email=%s ORDER BY id DESC LIMIT 1', (user_email,))
                row = cursor.fetchone()
                if row:
                    psychometric_results = row
                cursor.close()
                conn.close()
            except Exception as e:
                print(f"MySQL fetch error: {e}")

        # personalization = f"User profile: {user_profile}\nPsychometric test: {psychometric_results}\nFuture self: {futureself_results}\nExpenditure: {expenditure_data}"

        # === LangChain RAG: Try to answer using your own documents ===
        if rag_enabled:
            try:
                rag_result = qa_chain({"query": user_message})
                answer = rag_result.get("result", "").strip()
                sources = rag_result.get("source_documents", [])
                print(f"LangChain RAG answer: {answer}")
                if answer and len(answer.split()) > 5:
                    return jsonify({'response': answer})
            except Exception as rag_e:
                print(f"LangChain RAG error: {rag_e}")
       # === INTENT-BASED FALLBACK ===
        processed = preprocess_text(user_message)
        X = vectorizer.transform([processed]).toarray()
        with torch.no_grad():
            output = model(torch.tensor(X, dtype=torch.float32))
            predicted = torch.argmax(output, dim=1).item()
            tag = intents[predicted]
            responses = intent_responses.get(tag, ["I'm not sure how to help with that."])
            response = random.choice(responses)
        print(f"Intent fallback: tag={tag}, response={response}")
        return jsonify({'response': response})

    except Exception as e:
        print(f"Error: {e}")
        return jsonify({'response': f"An error occurred: {str(e)}"}), 500

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5002)

